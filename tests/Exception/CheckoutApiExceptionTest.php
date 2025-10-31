<?php

namespace CheckoutPaymentBundle\Tests\Exception;

use CheckoutPaymentBundle\Exception\CheckoutApiException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(CheckoutApiException::class)]
final class CheckoutApiExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionMessage(): void
    {
        $exception = new CheckoutApiException('API call failed');

        $this->assertEquals('API call failed', $exception->getMessage());
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testExceptionWithCode(): void
    {
        $exception = new CheckoutApiException('API error', 400);

        $this->assertEquals('API error', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
    }

    public function testExceptionWithPrevious(): void
    {
        $previous = new \Exception('Previous error');
        $exception = new CheckoutApiException('API error', 500, $previous);

        $this->assertEquals('API error', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testRequestFailed(): void
    {
        $exception = CheckoutApiException::requestFailed('Request failed');

        $this->assertEquals('Request failed', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertInstanceOf(CheckoutApiException::class, $exception);
    }

    public function testResponseError(): void
    {
        $exception = CheckoutApiException::responseError('Response error');

        $this->assertEquals('Response error', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertInstanceOf(CheckoutApiException::class, $exception);
    }

    public function testRequestFailedWithPrevious(): void
    {
        $previous = new \Exception('Previous error');
        $exception = CheckoutApiException::requestFailed('Request failed', $previous);

        $this->assertEquals('Request failed', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testResponseErrorWithPrevious(): void
    {
        $previous = new \Exception('Previous error');
        $exception = CheckoutApiException::responseError('Response error', $previous);

        $this->assertEquals('Response error', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
