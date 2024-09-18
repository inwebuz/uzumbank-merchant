<?php

namespace Inwebuz\UzumbankMerchant;

use Illuminate\Http\Request;
use Inwebuz\UzumbankMerchant\Models\UzumbankTransaction;

class UzumbankMerchant
{
    const ERROR_AUTH_FAILED = 10001; // Доступ запрещен 	Ошибка авторизации 
    const ERROR_PARSE_JSON = 10002; // Ошибка парсинга JSON объекта с параметрами запроса 	
    const ERROR_METHOD_ERROR = 10003; // Недопустимая операция 	Неверный HTTP метод
    const ERROR_REQUIRED_PARAMS_MISSING = 10005; // Отсутствуют обязательные параметры в запросе 	
    const ERROR_INCORRECT_SERVICE_ID = 10006; // Неверный serviceId 	
    const ERROR_PAYABLE_NOT_FOUND = 10007; // Дополнительный атрибут платежа не найден 	Например: номер лицевого счета, телефона или заказа / Masalan: shaxsiy hisob raqami, telefon yoki buyurtma raqami
    const ERROR_ALREADY_PAID = 10008; // Платеж уже оплачен 	Для указанных дополнительных атрибутов
    const ERROR_CANCELLED = 10009; // Платеж отменен 	Для указанных дополнительных атрибутов
    const ERROR_TRANSACTION_ALREADY_CREATED = 10010; // Транзакция с указанным идентификатором transId уже создана 	
    const ERROR_INCORRECT_AMOUNT = 10011; // Неверная сумма 	
    const ERROR_MIN_PRICE = 10012; // Сумма оплаты ниже минимальной 	
    const ERROR_MAX_PRICE = 10013; // Сумма оплаты превышает максимальную
    const ERROR_TRANSACTION_NOT_FOUND = 10014; // Транзакция не найдена
    const ERROR_TRANSACTION_CANCELLED = 10015; // Транзакция отменена
    const ERROR_TRANSACTION_ALREADY_CONFIRMED = 10016; // Транзакция с идентификатором transId уже подтверждена 	
    const ERROR_TRANSACTION_CANNOT_BE_REVERSED = 10017; // Невозможно отменить транзакцию 	
    const ERROR_TRANSACTION_ALREADY_REVERSED = 10018; // Транзакция с идентификатором transId уже отменена 	
    const ERROR_OTHER = 99999; // Ошибка проверки данных 	Сервис недоступен, повторите попытку позже

    public static function getPayableByParams($params)
    {
        $payable = null;
        $type = $params['type'] ?? null;
        $id = $params['id'] ?? null;
        $payableModels = config('uzumbankmerchant.payable_models');
        if ($type && isset($payableModels[$type]) && class_exists($payableModels[$type]) && $payableModels[$type] instanceof \Illuminate\Database\Eloquent\Model) {
            $payable = $payableModels[$type]::find($id);
        }
        return $payable;
    }
}