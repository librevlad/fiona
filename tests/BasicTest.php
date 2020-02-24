<?php

namespace Librevlad\Fiona\Tests;

use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase {

    public function testBasicApi() {
        $fiona = new \Librevlad\Fiona\Detector();
        $data  = $fiona->detect( 'Алина Борисовна Ыыва Sdfg Ывап Хотченкова' );
        $this->assertEquals( $data[ 'first_name' ], 'Иван' );
    }

}
