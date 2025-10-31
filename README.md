# Checkout.com Payment Bundle

[English](README.md) | [中文](README.zh-CN.md)

一个用于集成 Checkout.com 支付平台的 Symfony Bundle。

## 功能特性

- **Hosted Payments Page**: 托管支付页面集成
- **Direct API Payments**: 直接 API 支付集成
- **Webhook 处理**: 支付结果通知处理
- **完整的支付流程**: 创建会话、处理支付、状态同步
- **支付操作**: 捕获、退款、作废支付
- **实体管理**: 支付会话、支付记录、退款记录、Webhook 日志
- **API 集成**: 基于 Symfony HTTP Client 的 Checkout.com API 集成
- **安全验证**: Webhook 签名验证
- **完整的测试覆盖**: 单元测试和集成测试

## 支持的支付操作

### 支付生命周期
- **创建支付**: Hosted Payments 和 Direct API
- **捕获支付**: 对预授权支付进行捕获
- **退款支付**: 支持全额和部分退款
- **作废支付**: 作废未捕获的预授权支付

### 支付状态管理
- **Authorized**: 支付已授权（预授权）
- **Captured**: 支付已捕获
- **Refunded**: 支付已退款
- **Voided**: 支付已作废
- **Declined**: 支付被拒绝
- **Failed**: 支付失败

### 退款管理
- **全额退款**: 退还全部支付金额
- **部分退款**: 退还部分支付金额
- **多次退款**: 支持多次部分退款
- **退款追踪**: 详细记录退款历史和状态

## 安装

```bash
composer require hotel/checkout-payment-bundle
```

## 配置

### 1. 注册 Bundle

在 `config/bundles.php` 中添加：

```php
return [
    // ...
    Hotel\CheckoutPaymentBundle\HotelCheckoutPaymentBundle::class => ['all' => true],
];
```

### 2. 环境变量

在 `.env` 文件中添加：

```env
# Checkout.com API 配置
CHECKOUT_API_KEY=your_api_key_here
CHECKOUT_SANDBOX=true
CHECKOUT_WEBHOOK_SECRET=your_webhook_secret_here
```

### 3. 数据库配置

运行数据库迁移：

```bash
php bin/console doctrine:schema:update --force
```

## 使用方法

### 创建 Hosted Payment 会话

```php
use Hotel\CheckoutPaymentBundle\Service\PaymentService;

class PaymentController extends AbstractController
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function createHostedPayment(): Response
    {
        $data = [
            'reference' => 'order_123',
            'amount' => 1000, // 以分为单位
            'currency' => 'USD',
            'customer_email' => 'customer@example.com',
            'customer_name' => 'John Doe',
            'success_url' => 'https://your-domain.com/payment/success',
            'cancel_url' => 'https://your-domain.com/payment/cancel',
            'failure_url' => 'https://your-domain.com/payment/failure',
            'description' => 'Order #123',
            'billing_address' => [
                'address_line1' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'zip' => '10001',
                'country' => 'US'
            ],
            'metadata' => [
                'order_id' => '123',
                'customer_id' => '456'
            ]
        ];

        $session = $this->paymentService->createPaymentSession($data);

        // 重定向到支付页面
        return $this->redirect($session->getPaymentUrl());
    }
}
```

### 创建 Direct API 支付

```php
public function createDirectPayment(): JsonResponse
{
    $paymentData = [
        'reference' => 'order_123',
        'amount' => 1000,
        'currency' => 'USD',
        'source' => [
            'type' => 'card',
            'number' => '4242424242424242',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvv' => '100'
        ],
        'customer' => [
            'email' => 'customer@example.com',
            'name' => 'John Doe'
        ],
        'billing_address' => [
            'address_line1' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'zip' => '10001',
            'country' => 'US'
        ]
    ];

    $payment = $this->paymentService->createDirectPayment($paymentData);

    return new JsonResponse([
        'payment_id' => $payment->getPaymentId(),
        'status' => $payment->getStatus()
    ]);
}
```

### 捕获支付

```php
public function capturePayment(string $paymentId): JsonResponse
{
    $captureData = [
        'amount' => 1000, // 可选，默认为全额捕获
        'reference' => 'capture_123'
    ];

    $payment = $this->paymentService->capturePayment($paymentId, $captureData);

    return new JsonResponse([
        'payment_id' => $payment->getPaymentId(),
        'status' => $payment->getStatus(),
        'captured_time' => $payment->getCapturedAt()->format('Y-m-d H:i:s')
    ]);
}
```

### 退款支付

```php
public function refundPayment(string $paymentId): JsonResponse
{
    $refundData = [
        'amount' => 500, // 部分退款
        'reference' => 'refund_123',
        'reason' => 'Customer request'
    ];

    $payment = $this->paymentService->refundPayment($paymentId, $refundData);

    return new JsonResponse([
        'payment_id' => $payment->getPaymentId(),
        'status' => $payment->getStatus(),
        'refunded_amount' => $payment->getRefundedAmount(),
        'available_refund_amount' => $payment->getAvailableRefundAmount()
    ]);
}
```

### 作废支付

```php
public function voidPayment(string $paymentId): JsonResponse
{
    $payment = $this->paymentService->voidPayment($paymentId);

    return new JsonResponse([
        'payment_id' => $payment->getPaymentId(),
        'status' => $payment->getStatus(),
        'voided_time' => $payment->getVoidedAt()->format('Y-m-d H:i:s')
    ]);
}
```

### 搜索支付

```php
public function searchPayments(): JsonResponse
{
    $filters = [
        'reference' => 'order_123',
        'status' => 'Captured',
        'limit' => 50,
        'skip' => 0
    ];

    $payments = $this->paymentService->searchPayments($filters);

    return new JsonResponse([
        'filters' => $filters,
        'payments' => $payments
    ]);
}
```

### 处理 Webhook

```php
use Hotel\CheckoutPaymentBundle\Controller\WebhookController;

// Webhook 会自动处理，只需配置路由
// POST /api/checkout-payment/webhooks
```

### 获取支付历史

```php
$history = $this->paymentService->getPaymentHistory('order_123');
```

## API 端点

### 支付会话管理

- `POST /api/checkout-payment/sessions` - 创建 Hosted Payment 会话
- `GET /api/checkout-payment/sessions/{sessionId}` - 获取支付会话
- `POST /api/checkout-payment/sessions/{sessionId}/sync` - 同步支付状态

### Direct API 支付

- `POST /api/checkout-payment/payments` - 创建 Direct API 支付

### 支付操作

- `POST /api/checkout-payment/payments/{paymentId}/capture` - 捕获支付
- `POST /api/checkout-payment/payments/{paymentId}/refund` - 退款支付
- `POST /api/checkout-payment/payments/{paymentId}/void` - 作废支付

### 支付查询

- `GET /api/checkout-payment/payments/{paymentId}/actions` - 获取支付操作历史
- `GET /api/checkout-payment/payments/{paymentId}/refunds` - 获取支付退款记录
- `GET /api/checkout-payment/payments/{paymentId}/captures` - 获取支付捕获记录
- `GET /api/checkout-payment/payments/{paymentId}/voids` - 获取支付作废记录
- `GET /api/checkout-payment/payments/search` - 搜索支付记录

### 支付历史

- `GET /api/checkout-payment/payments/{reference}/history` - 获取支付历史

### Webhook

- `POST /api/checkout-payment/webhooks` - 处理 Webhook 通知
- `POST /api/checkout-payment/webhooks/validate` - 验证 Webhook 请求

### 支付结果页面

- `GET /checkout-payment/success` - 支付成功页面
- `GET /checkout-payment/cancel` - 支付取消页面
- `GET /checkout-payment/failure` - 支付失败页面

## 支付流程

1. **创建支付会话**: 调用 API 创建 Hosted Payments Session
2. **重定向用户**: 将用户重定向到 Checkout.com 支付页面
3. **用户支付**: 用户在托管页面完成支付
4. **Webhook 通知**: Checkout.com 发送支付结果通知
5. **状态更新**: 系统自动更新支付状态

## 实体结构

### PaymentSession
- 支付会话信息
- 包含订单信息、金额、货币等
- 关联到多个支付记录
- 提供支付状态汇总方法

### Payment
- 支付记录
- 包含支付状态、响应信息、时间戳等
- 关联到支付会话和多个退款记录
- 提供支付状态检查和业务逻辑方法

### PaymentRefund
- 退款记录
- 包含退款金额、原因、状态等
- 关联到支付记录
- 支持多次退款和状态追踪

### WebhookLog
- Webhook 日志
- 记录所有 Webhook 请求
- 包含签名验证结果和处理状态

## 支付状态检查

### Payment 实体方法
```php
// 检查支付状态
$payment->isApproved();     // 是否已授权
$payment->isCaptured();     // 是否已捕获
$payment->isRefunded();     // 是否已退款
$payment->isVoided();       // 是否已作废

// 检查是否可以执行操作
$payment->canCapture();     // 是否可以捕获
$payment->canRefund();      // 是否可以退款
$payment->canVoid();        // 是否可以作废

// 获取可退款金额
$payment->getAvailableRefundAmount();

// 获取退款统计
$payment->getTotalRefundedAmount();
$payment->getApprovedRefunds();
$payment->getPendingRefunds();
$payment->getFailedRefunds();
```

### PaymentSession 实体方法
```php
// 检查支付会话状态
$session->isPaid();              // 是否已支付
$session->getSuccessfulPayment(); // 获取成功支付记录
$session->getLatestPayment();     // 获取最新支付记录

// 获取退款统计
$session->getTotalRefundedAmount();
$session->getNetAmount();
```

## 命令行工具

### 配置管理命令

#### checkout:config:list
列出所有启用的 Checkout 支付配置

```bash
# 显示所有启用的配置
php bin/console checkout:config:list
```

该命令会显示以下信息：
- 配置名称
- 配置描述
- 运行环境（沙箱/生产）
- 超时设置
- 重试次数
- 是否为默认配置

输出示例：
```
Checkout 支付配置列表
┌──────────────┬─────────────────┬────────┬────────┬──────────┬──────────┐
│ 名称         │ 描述            │ 环境   │ 超时   │ 重试次数 │ 是否默认 │
├──────────────┼─────────────────┼────────┼────────┼──────────┼──────────┤
│ default      │ 默认配置        │ 沙箱   │ 30s    │ 3        │ ✓        │
│ production   │ 生产环境配置    │ 生产   │ 60s    │ 5        │ ✗        │
└──────────────┴─────────────────┴────────┴────────┴──────────┴──────────┘

共找到 2 个 Checkout 配置
```

## 测试

```bash
# 运行所有测试
php bin/phpunit tests/ --stop-on-failure

# 运行特定测试
php bin/phpunit tests/Service/PaymentServiceTest.php
php bin/phpunit tests/Service/PaymentServiceExtendedTest.php
php bin/phpunit tests/Controller/PaymentControllerTest.php
php bin/phpunit tests/Entity/PaymentTest.php
php bin/phpunit tests/Entity/PaymentRefundTest.php

# 运行静态分析
php bin/phpstan analyse src/ --level=8

# 检查语法
php -l src/Entity/Payment.php
php -l src/Entity/PaymentRefund.php
php -l src/Service/PaymentService.php
php -l src/Controller/PaymentController.php
```

## 安全特性

- **Webhook 签名验证**: 验证所有 Webhook 请求的真实性
- **HTTPS 强制**: 所有 API 调用使用 HTTPS
- **输入验证**: 严格的输入数据验证
- **错误处理**: 完善的错误处理和日志记录
- **PCI DSS 合规**: 符合支付卡行业数据安全标准
- **3D Secure 支持**: 自动支持 3D Secure 认证
- **欺诈检测**: 集成 Checkout.com 欺诈检测功能

## 错误处理

### 常见错误码
- `20014` - 无效的金额
- `20015` - 无效的货币
- `20016` - 无效的参考号
- `20017` - 无效的客户邮箱
- `20018` - 无效的账单地址
- `20022` - 支付被拒绝
- `20023` - 支付处理失败

### 业务逻辑错误
- 支付状态不允许执行操作
- 退款金额超过可退款金额
- 捕获金额超过支付金额
- 支付会话或支付记录不存在

## 环境配置

### 生产环境

```env
CHECKOUT_API_KEY=your_production_api_key
CHECKOUT_SANDBOX=false
CHECKOUT_WEBHOOK_SECRET=your_production_webhook_secret
```

### 测试环境

```env
CHECKOUT_API_KEY=your_sandbox_api_key
CHECKOUT_SANDBOX=true
CHECKOUT_WEBHOOK_SECRET=your_sandbox_webhook_secret
```

## 支持的支付方式

### 信用卡
- Visa
- Mastercard
- American Express
- Discover
- JCB
- Diners Club

### 数字钱包
- Apple Pay
- Google Pay
- Samsung Pay

### 本地支付方式
- Alipay (支付宝)
- WeChat Pay (微信支付)
- iDEAL (荷兰)
- Sofort (德国)
- Giropay (德国)
- EPS (奥地利)
- Multibanco (葡萄牙)
- PSE (哥伦比亚)

### 其他支付方式
- 银行转账
- 分期付款
- BNPL (先买后付)

## 性能优化

### 数据库优化
- 适当的索引设计
- 查询优化
- 批量操作支持

### API 调用优化
- 请求缓存
- 并发控制
- 重试机制

### 内存管理
- 合理的实体关系设计
- 及时清理不必要的数据

## 监控和日志

### 日志记录
- 支付操作日志
- API 调用日志
- 错误日志
- Webhook 处理日志

### 监控指标
- 支付成功率
- API 响应时间
- 错误率
- 系统资源使用

### 告警设置
- 支付失败告警
- API 异常告警
- 系统异常告警

## 许可证

MIT License

## 贡献

欢迎提交 Issue 和 Pull Request。

### 开发指南
1. Fork 项目
2. 创建功能分支
3. 编写测试
4. 确保所有测试通过
5. 提交 Pull Request

### 代码规范
- 遵循 PSR-12 编码标准
- 编写完整的测试用例
- 添加适当的文档
- 确保代码安全

### 问题报告
- 使用 GitHub Issues
- 提供详细的复现步骤
- 包含错误日志和环境信息
- 指定影响的版本号