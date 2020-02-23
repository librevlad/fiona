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

    public function detect( $fio ) {

        $segments = explode( ' ', $fio );

        $return = [
            'first_name'         => null,
            'last_name'          => null,
            'patronymic'         => null,
            'gender'             => null,
            'unmatched_segments' => [],
        ];


        $possibilitiesBySegment = $this->getPossibilitiesBySegment( $segments );


//        dd( $possibilitiesBySegment );

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

    protected function getPossibilitiesBySegment( array $segments ) {
        $possibilitiesBySegment = [];
        foreach ( $segments as $k => $segment ) {
            $possibilities             = $this->getPossibilitiesForSegment( $segment );
            $possibilitiesBySegment [] = $possibilities;
        }

        return $possibilitiesBySegment;
    }
}
