<?php

namespace CheckoutPaymentBundle\Tests\Command;

use CheckoutPaymentBundle\Command\CheckoutConfigListCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(CheckoutConfigListCommand::class)]
#[RunTestsInSeparateProcesses]
class CheckoutConfigListCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // Command tests don't need special setup
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(CheckoutConfigListCommand::class);

        $application = new Application();
        $application->add($command);

        return new CommandTester($application->find('checkout:config:list'));
    }

    public function testCommandExecutionWithConfigs(): void
    {
        $command = self::getService(CheckoutConfigListCommand::class);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('checkout:config:list'));
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Checkout 支付配置列表', $output);
        $this->assertStringContainsString('Test Checkout Config', $output);
        $this->assertStringContainsString('Test configuration for development', $output);
        $this->assertStringContainsString('沙箱', $output);
        $this->assertStringContainsString('30s', $output);
        $this->assertStringContainsString('3', $output);
        $this->assertStringContainsString('✓', $output);
        $this->assertStringContainsString('共找到 1 个 Checkout 配置', $output);
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testCommandDisplaysTableHeaders(): void
    {
        $command = self::getService(CheckoutConfigListCommand::class);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('checkout:config:list'));
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('名称', $output);
        $this->assertStringContainsString('描述', $output);
        $this->assertStringContainsString('环境', $output);
        $this->assertStringContainsString('超时', $output);
        $this->assertStringContainsString('重试次数', $output);
        $this->assertStringContainsString('是否默认', $output);
        $this->assertSame(0, $commandTester->getStatusCode());
    }
}
