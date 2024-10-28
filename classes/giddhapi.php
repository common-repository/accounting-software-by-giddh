<?php
require("apirequest.php");

class GiddhApi extends GiddhApiRequest {

    public static $apiUrl = GIDDH_API_URL;

    public function sendApiResponse($response) {
        if($response && $response["code"] == "INVALID_AUTH_KEY") {
            giddhDisconnectApp();
            return $response;
        } else {
            return $response;
        }
    }

    public function verifyAccount($companyUniqueName, $parameters) {
        return GiddhApiRequest::jsonPost(self::$apiUrl."/company/".$companyUniqueName."/ecommerce/users", $parameters);
    }

    public function createAccount($companyUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPost(self::$apiUrl."/v2/company/".$companyUniqueName."/groups/sundrydebtors/accounts", $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function createSalesInvoice($companyUniqueName, $accountUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPost(self::$apiUrl."/v4/company/".$companyUniqueName."/accounts/".$accountUniqueName."/vouchers/generate", $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function updateSalesInvoice($companyUniqueName, $accountUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPut(self::$apiUrl."/v4/company/".$companyUniqueName."/accounts/".$accountUniqueName."/vouchers", $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function actionSalesInvoice($companyUniqueName, $invoiceUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPost(self::$apiUrl."/company/".$companyUniqueName."/invoices/".$invoiceUniqueName."/action", $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function createCashInvoice($companyUniqueName, $accountUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPost(self::$apiUrl."/v4/company/".$companyUniqueName."/accounts/".$accountUniqueName."/vouchers/generate", $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function updateCashInvoice($companyUniqueName, $accountUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPut(self::$apiUrl."/v4/company/".$companyUniqueName."/accounts/".$accountUniqueName."/vouchers", $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function getWarehouses($companyUniqueName, $authKey) {
        $response = GiddhApiRequest::get(self::$apiUrl."/company/".$companyUniqueName."/warehouse?page=1&count=500&refresh=true", $authKey);
        return $this->sendApiResponse($response);
    }

    public function getBankAccounts($companyUniqueName, $authKey) {
        $response = GiddhApiRequest::get(self::$apiUrl."/v2/company/".$companyUniqueName."/groups/bankaccounts/accounts", $authKey);
        return $this->sendApiResponse($response);
    }

    public function getInvoiceDetails($companyUniqueName, $accountUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPost(self::$apiUrl."/v4/company/".$companyUniqueName."/accounts/".$accountUniqueName."/vouchers", $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function disconnectAccount($companyUniqueName, $shopUniqueName, $parameters) {
        return GiddhApiRequest::jsonPost(self::$apiUrl."/company/".$companyUniqueName."/ecommerce/disconnect/".$shopUniqueName, $parameters);
    }

    public function getCountryDetails($countryCode, $authKey) {
        $response = GiddhApiRequest::get(self::$apiUrl."/country/".$countryCode, $authKey);
        return $this->sendApiResponse($response);
    }

    public function createWarehouse($companyUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPost(self::$apiUrl."/company/".$companyUniqueName."/warehouse", $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function createBankAccount($companyUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPost(self::$apiUrl."/company/".$companyUniqueName."/groups/bankaccounts/accounts", $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function getStockGroups($companyUniqueName, $authKey, $search) {
        $response = GiddhApiRequest::get(self::$apiUrl."/company/".$companyUniqueName."/flatten-stock-groups-with-stocks?q=".$search."&page=1&count=10", $authKey);
        return $this->sendApiResponse($response);
    }

    public function createStockGroup($companyUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPost(self::$apiUrl."/company/".$companyUniqueName."/stock-group", $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function sendEmail($companyUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPost(self::$apiUrl."/company/".$companyUniqueName."/ecommerce/email", $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function getAllStock($companyUniqueName, $authKey, $page) {
        $response = GiddhApiRequest::get(self::$apiUrl."/company/".$companyUniqueName."/stocks?page=".$page."&count=".GIDDH_PAGINATION_LIMIT, $authKey);
        return $this->sendApiResponse($response);
    }

    public function createBulkStock($companyUniqueName, $eCommerceUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPost(self::$apiUrl."/company/".$companyUniqueName."/sync-data/".$eCommerceUniqueName."/stocks", $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function getStock($companyUniqueName, $stockGroupUniqueName, $stockUniqueName, $authKey) {
        $response = GiddhApiRequest::get(self::$apiUrl."/company/".$companyUniqueName."/stock-group/".$stockGroupUniqueName."/stock/".$stockUniqueName, $authKey);
        return $this->sendApiResponse($response);
    }

    public function updateStock($companyUniqueName, $stockGroupUniqueName, $stockUniqueName, $authKey, $parameters) {
        $response = GiddhApiRequest::jsonPut(self::$apiUrl."/company/".$companyUniqueName."/stock-group/".$stockGroupUniqueName."/stock/".$stockUniqueName, $parameters, $authKey);
        return $this->sendApiResponse($response);
    }

    public function getSalesAccounts($companyUniqueName, $authKey, $search) {
        $response = GiddhApiRequest::get(self::$apiUrl."/company/".$companyUniqueName."/groups-with-accounts?q=".$search, $authKey);
        return $this->sendApiResponse($response);
    }
}
?>