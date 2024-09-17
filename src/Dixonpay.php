<?php

namespace Orwallet\DixonpaySdk;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Dixonpay
{
    private string $api_url;
    private string $three_ds_api_url;
    private string $refund_api_url;
    private Collection $headers;
    protected Collection $vault;
    private array $payload;

    public function __construct(array $vault)
    {
        $this->setVault($vault);
    }

    public function setHeaders(array $headers = [])
    {
        $this->headers->push($headers);

        return $this;
    }

    public function setPayload(array $payload, bool $refund = false)
    {
        $validator = Validator::make($payload, !$refund
            ? [
                "address" => "required|string",
                "card_expire_month" => "required|string",
                "card_expire_year" => "required|string",
                "card_no" => "required|string",
                "card_security_code" => "required|string",
                "city" => "nullable|string",
                "country" => "nullable|string",
                "email" => "nullable|email:strict,dns",
                "encryption" => "required|string",
                "first_name" => "nullable|string",
                "last_name" => "nullable|string",
                "ip_address" => "string",
                "order_amount" => "required|string",
                "order_currency" => "required|string",
                "order_no" => "required|string",
                "phone" => "nullable|string",
                "state" => "nullable|string",
                "zip" => "nullable|string",
                "notify_url" => "required|string|url:https",
                "return_url" =>  "nullable|string|url:https",
            ] : [
                "encryption" => "required|string",
                "currency" => "required|string",
                "order_no" => "order_no",
                "refund_amount" => "required|string",
                "refund_reason" => "required|string",
                "trade_amount" => "required|string",
                "trade_no" => "required|string",
            ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $payload = collect($validator->validated());

        $this->payload = !$refund ? [
            "address" => $payload->get("address", ""),
            "cardExpireMonth" => $payload->get("card_expire_month"),
            "cardExpireYear" => $payload->get("card_expire_year"),
            "cardNo" => $payload->get("card_no"),
            "cardSecurityCode" => $payload->get("card_security_code", ""),
            "city" => $payload->get("city", ""),
            "country" => $payload->get("country", ""),
            "email" => $payload->get("email", ""),
            "encryption" =>  $payload->get("hash"),
            "firstName" => $payload->get("first_name", ""),
            "ip" => $payload->get("ip_address"),
            "lastName" => $payload->get("last_name", ""),
            "merNo" => $this->vault->get("mer_no"),
            "orderAmount" => $payload->get("order_amount"),
            "orderCurrency" => $payload->get("order_currency", ""),
            "orderNo" => $payload->get("order_no"),
            "phone" => $payload->get("phone", ""),
            "state" => $payload->get("state", ""),
            "terminalNo" => $this->vault->get("terminal_no"),
            "webSite" => $this->vault->get("registered_website"),
            "zip" => $payload->get("zip"),
            "notifyUrl" => $payload->get("notify_url"),
            "returnUrl" => $payload->get("return_url"),
        ] : [
            "json" => [
                "merNo" => $this->vault->get("mer_no"),
                "terminalNo" =>  $this->vault->get("terminal_no"),
                "encryption" =>  $payload->get("hash"),
                "refundOrders" => [
                    [
                        "currency" => $payload->get("currency"),
                        "orderNo" => $payload->get("order_no"),
                        "refundAmount" => $payload->get("refund_amount"),
                        "refundReason" => $payload->get("refund_reason"),
                        "tradeAmount" => $payload->get("trade_amount"),
                        "tradeNo" => $payload->get("trade_no"),
                    ],
                ],
            ],
        ];

        return $this;
    }

    public function setVault(array $vault): void
    {
        $validator = Validator::make($vault, [
            "mid" => "required|string",
            "terminal_number" => "required|string",
            "sign_key" => "required|string",
            "registered_website" => "required|string",
            "api_url" => "required|url,https",
            "api3ds_url" => "required|url,https",
            "refund_api_url" => "required|url,https",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->vault->push($validator->validated());
    }

    public function request()
    {
        return $this->http()->post($this->api_url, $this->payload);
    }

    public function request3ds()
    {
        return $this->http()->post($this->three_ds_api_url, $this->payload);
    }

    public function requestRefund()
    {
        return $this->http()->post($this->refund_api_url, $this->payload);
    }

    private function http()
    {
        return Http::withHeaders($this->headers)->asForm();
    }
}
