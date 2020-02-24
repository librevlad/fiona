<?php


namespace Librevlad\Fiona;


use Illuminate\Support\Arr;

class Detector {

    public $db;

    public function __construct( $db = null ) {
        if ( ! $db ) {
            $db = json_decode( file_get_contents( __DIR__ . '/../db.json' ), true );
        }
        $this->db = $db;
    }

    public function detect( $fio, $strict = false ) {

        $fio = mb_convert_case( $fio, MB_CASE_TITLE, 'UTF-8' );
        $fio = str_replace( '  ', ' ', $fio );
        $fio = str_replace( '  ', ' ', $fio );

        $segments = explode( ' ', trim( $fio ) );

        $return = [
            'first_name'         => null,
            'last_name'          => null,
            'patronymic'         => null,
            'gender'             => null,
            'unmatched_segments' => [],
        ];


        $possibilitiesBySegment = $this->getPossibilitiesBySegment( $segments );
        $stats                  = $this->stats( $possibilitiesBySegment );

        $return [ 'gender' ] = $stats[ 'gender' ];
        $stats               = $stats[ 'stats' ];


        while ( $cs = $this->bestConfidenceScore( $stats ) ) {

            if ( ! $cs[ 'segment' ] ) {
                break;
            }

            if ( ! $return[ $cs[ 'segment' ] ] ) {
                $return[ $cs[ 'segment' ] ] = $cs[ 'value' ];
            }

            foreach ( $stats as $segment => &$values ) {
                $newValues = [];
                foreach ( $values as $k => $v ) {
                    if ( $k != $cs[ 'value' ] ) {
                        $newValues [ $k ] = $v;
                    }
                }
                $values = $newValues;
            }

        }

        $matched   = array_intersect( $segments, [
            $return[ 'first_name' ],
            $return[ 'last_name' ],
            $return[ 'patronymic' ],
        ] );
        $unmatched = array_diff( $segments, $matched );
        if ( ! $strict ) {
            if ( count( $unmatched ) && ! $return[ 'last_name' ] ) {
                $return[ 'last_name' ] = array_shift( $unmatched );
            }
            if ( count( $unmatched ) && ! $return[ 'patronymic' ] ) {
                $return[ 'patronymic' ] = array_shift( $unmatched );
            }
        }

        $return[ 'unmatched_segments' ] = $unmatched;

        return $return;

    }

    protected function getPossibilitiesForSegment( $segment ) {
        $possibilities = [
            'segment' => $segment,
            'female'  => [
                'first_name' => 0,
                'patronymic' => 0,
                'last_name'  => 0,
            ],
            'male'    => [
                'first_name' => 0,
                'patronymic' => 0,
                'last_name'  => 0,
            ],
        ];

        foreach ( [ 'male', 'female' ] as $gender ) {
            foreach ( $this->db[ $gender ] as $typeName => $type ) {
                $pop                                   = Arr::get( $type, $segment . '.popularity', 0 );
                $possibilities[ $gender ][ $typeName ] = $pop;
            }
        }

        return $possibilities;

    }

    public function better( $gender, $segment, $value1, $value2 ) {
        $pop1 = $this->popularity( $gender, $segment, $value1 );
        $pop2 = $this->popularity( $gender, $segment, $value2 );

        return $pop1 > $pop2 ? $value1 : $value2;
    }

    public function popularity( $gender, $segment, $value ) {
        return $this->db[ $gender ][ $segment ][ $value ] ?? 0;
    }

    public function mostPopular( $gender, $segment, $count ) {

        return array_keys( array_slice( $this->db[ $gender ][ $segment ], 0, $count ) );
    }

    public function stats( $possibilities_by_segment ) {

        $femaleScore = 0;
        $maleScore   = 0;

        foreach ( $possibilities_by_segment as $segment ) {
            $femaleScore += array_sum( $segment[ 'female' ] );
            $maleScore   += array_sum( $segment[ 'male' ] );
        }

        $gender = ( $maleScore > $femaleScore ) ? 'male' : 'female';

        $stats = [
            'first_name' => [],
            'last_name'  => [],
            'patronymic' => [],
        ];

        foreach ( $possibilities_by_segment as $segment ) {

            $stats[ 'first_name' ][ $segment[ 'segment' ] ] = $segment[ $gender ][ 'first_name' ];
            $stats[ 'last_name' ][ $segment[ 'segment' ] ]  = $segment[ $gender ][ 'last_name' ];
            $stats[ 'patronymic' ][ $segment[ 'segment' ] ] = $segment[ $gender ][ 'patronymic' ];

        }

        return [
            'stats'  => $stats,
            'gender' => $gender,
        ];

    }

    protected function getPossibilitiesBySegment( array $segments ) {
        $possibilitiesBySegment = [];
        foreach ( $segments as $k => $segment ) {
            $possibilities             = $this->getPossibilitiesForSegment( $segment );
            $possibilitiesBySegment [] = $possibilities;
        }


        return $possibilitiesBySegment;
    }

    private function bestConfidenceScore( $stats ) {
        if ( ! $stats ) {
            return false;
        }
        $best = [
            'score'   => - 1,
            'segment' => null,
            'value'   => null,
        ];
        foreach ( $stats as $segment => $s ) {
            foreach ( $s as $value => $score ) {
                if ( $score > $best[ 'score' ] ) {
                    $best = [
                        'score'   => $score,
                        'segment' => $segment,
                        'value'   => $value,
                    ];
                }
            }
        }

        return $best;
    }
}
