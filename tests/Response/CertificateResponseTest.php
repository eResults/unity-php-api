<?php

namespace eResults\Unity\Test\Api\Response;

use eResults\Unity\Api\Client;
use eResults\Unity\Api\Response\ObjectResponse;

class CertificateResponseTest
    extends \PHPUnit_Framework_TestCase
{
    public function testPending()
    {
        $response = [
            ObjectResponse::factory(
                new Client(),
                'certificate',
                [
                    'id' => 'aaaa-aaaa-aaaa-aaaa',
                    'status' => 'pending',
                    'name' => 'test',
                ]
            ),
            ObjectResponse::factory(
                new Client(),
                'certificate',
                [
                    'id' => 'bbbb-bbbb-bbbb-bbbb',
                    'status' => 'validated',
                    'name' => 'test 2',
                ]
            ),
        ];
        foreach ($response as $certificate) {
            $this->assertInstanceOf('\eResults\Unity\Api\Response\CertificateResponse', $certificate);
        }

        $this->assertTrue($response[0]->isPending());
        $this->assertNotTrue($response[1]->isPending());
    }
}