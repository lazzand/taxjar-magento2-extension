<?php
/**
 * Taxdoo_VAT
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2017 TaxJar. TaxJar is a trademark of TPS Unlimited, Inc. (http://www.taxjar.com)
 * @copyright  Copyright (c) 2021 Andrea Lazzaretti.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Taxdoo\VAT\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Pricing\PriceCurrencyInterface as PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Taxdoo\VAT\Model\Configuration as TaxdooConfig;

class Data extends AbstractHelper
{
    protected $request;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param Context $context
     * @param Http $request
     * @param ProductMetadataInterface $productMetadata
     * @param PriceCurrencyInterface $priceCurrency,
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Http $request,
        ProductMetadataInterface $productMetadata,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->request = $request;
        $this->productMetadata = $productMetadata;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context);
    }

    /*
     * Return an obfuscated version of an email address.
     * Many thanks to msturdy from stackoverflow for this function : https://stackoverflow.com/a/20545505
     */
     // I'm putting an ignore here, because one shouldn't use a static functions.
     // @codingStandardsIgnoreStart

    public static function obfuscateEmail($email)
    {
        $em   = explode("@", $email);
        $name = implode('@', array_slice($em, 0, count($em)-1));
        $len = 0;
        $asterisks = strlen($name);
        if (strlen($name) >= 3) {
            $len = 3;
            $asterisks = strlen($name) - 3;
        }

        return substr($name, 0, $len) . str_repeat('*', $asterisks) . "@" . end($em);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Transaction Sync enabled check
     *
     * @param int $scopeCode
     * @param string $scope
     * @return bool
     */
    public function isTransactionSyncEnabled($scopeCode = 0, $scope = ScopeInterface::SCOPE_STORE)
    {
        $scopeCode = $scopeCode ?: (int) $this->request->getParam($scope, 0);
        $syncEnabled = $this->scopeConfig->getValue(TaxdooConfig::TAXDOO_TRANSACTION_SYNC, $scope, $scopeCode);
        return (bool) $syncEnabled;
    }

    /**
     * Return a custom user agent string
     *
     * @return string
     */
    public function getUserAgent()
    {
        $disabledFunctions = explode(',', ini_get('disable_functions'));
        $os = !in_array('php_uname', $disabledFunctions) ? php_uname('a') : '';
        $php = 'PHP ' . PHP_VERSION;
        // @codingStandardsIgnoreStart
        // Magento deprecates curl_version - how shall we replace it?
        $curl = !in_array('curl_version', $disabledFunctions) ? 'cURL ' . curl_version()['version'] : '';
        // @codingStandardsIgnoreEnd
        $openSSL = defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : '';
        $magento = 'Magento ' . $this->productMetadata->getEdition() . ' ' . $this->productMetadata->getVersion();
        $precision = 'Precision ' . PriceCurrencyInterface::DEFAULT_PRECISION;
        $taxdoo = 'Taxdoo_VAT/' . TaxdooConfig::TAXDOO_VERSION;

        return "Taxdoo/Magento ($os; $php; $curl; $openSSL; $precision; $magento) $taxdoo";
    }

    /**
     * Return the base url of the current store
     *
     * @return string
     */
    public function getStoreUrl()
    {
        return (string) $this->storeManager->getStore()->getBaseUrl();
    }
}
