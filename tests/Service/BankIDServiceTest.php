<?php

namespace Dimafe6\BankID\Service;

use Dimafe6\BankID\Model\CollectResponse;
use Dimafe6\BankID\Model\OrderResponse;
use PHPUnit\Framework\TestCase;

class BankIDServiceTest extends TestCase
{
    /**
     * @var BankIDService $bankIDService
     */
    private $bankIDService;

    public function setUp(): void
    {
        $this->bankIDService = new BankIDService(
            'https://appapi2.test.bankid.com/rp/v6.0/',
            isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '127.0.0.1',
            [
                'verify' => false,
                'cert'   => [realpath(__DIR__ . '/../FPTestcert5_20240610.p12'), "qwerty123"] // qwerty123 = default password from BankID,
            ]
        );
    }

    /**
     * @return OrderResponse
     */
    public function testSignResponse()
    {
        $signResponse = $this->bankIDService->getSignResponse('userVisibleData');

        $this->assertInstanceOf(OrderResponse::class, $signResponse);

        return $signResponse;
    }


    /**
     * @depends testSignResponse
     * @param OrderResponse $signResponse
     * @return \Dimafe6\BankID\Model\CollectResponse
     */
    public function testCollectSignResponse($signResponse)
    {
        $this->assertInstanceOf(OrderResponse::class, $signResponse);

        $attempts = 0;
        do {
            fwrite(STDOUT, "\nWaiting confirmation from BankID application...\n");
            // sleep(2);
            $collectResponse = $this->bankIDService->collectResponse($signResponse->orderRef);
            $attempts++;
        } while ($collectResponse->status !== CollectResponse::HINT_PENDING_OUTSTANDING_TRANSACTION && $attempts <= 6);

        $this->assertInstanceOf(CollectResponse::class, $collectResponse);
        // $this->assertEquals(CollectResponse::STATUS_PENDING, $collectResponse->status);

        return $collectResponse;
    }

    /**
     * @depends testAuthResponse
     * @param OrderResponse $authResponse
     * @return \Dimafe6\BankID\Model\CollectResponse
     */
    public function testCollectAuthResponse($authResponse)
    {
        $this->assertInstanceOf(OrderResponse::class, $authResponse);

        $attempts = 0;
        do {
            fwrite(STDOUT, "\nWaiting confirmation from BankID application...\n");
            // sleep(2);
            $collectResponse = $this->bankIDService->collectResponse($authResponse->orderRef);
            $attempts++;
        } while ($collectResponse->status !== CollectResponse::HINT_PENDING_OUTSTANDING_TRANSACTION && $attempts <= 6);

        $this->assertInstanceOf(CollectResponse::class, $collectResponse);
        // $this->assertEquals(CollectResponse::STATUS_PENDING, $collectResponse->status);

        return $collectResponse;
    }

    /**
     * @return OrderResponse
     */
    public function testAuthResponse()
    {
        $authResponse = $this->bankIDService->getAuthResponse();

        $this->assertInstanceOf(OrderResponse::class, $authResponse);

        return $authResponse;
    }

    public function testCancelResponse()
    {
        $authResponse = $this->bankIDService->getAuthResponse();

        $this->assertInstanceOf(OrderResponse::class, $authResponse);

        sleep(3);

        $cancelResponse = $this->bankIDService->cancelOrder($authResponse->orderRef);

        $this->assertTrue($cancelResponse);
    }

}
