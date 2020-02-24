<?php

namespace Librevlad\Fiona\Tests;

use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase {

    public function testBasicApi() {
        $fiona = new \Librevlad\Fiona\Detector();
        $data  = $fiona->detect( 'Петров Иван Сергеевич' );
        $this->assertEquals( $data[ 'first_name' ], 'Иван' );
        $this->assertEquals( $data[ 'last_name' ], 'Петров' );
        $this->assertEquals( $data[ 'patronymic' ], 'Сергеевич' );
    }

}
