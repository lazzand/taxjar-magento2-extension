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
 * @category   Taxdoo
 * @package    Taxdoo_VAT
 * @copyright  Copyright (c) 2017 TaxJar. TaxJar is a trademark of TPS Unlimited, Inc. (http://www.taxjar.com)
 * @copyright  Copyright (c) 2021 Andrea Lazzaretti.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Taxdoo\VAT\Controller\Adminhtml\Transaction;

use Magento\Framework\Controller\ResultFactory;

class Backfill extends \Taxdoo\VAT\Controller\Adminhtml\Transaction
{
    /**
     * Sync transactions
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->logger->record();

            $this->eventManager->dispatch('taxdoo_vat_backfill_transactions');

            $responseContent = [
                'success' => true,
                'error_message' => '',
                'result' => $this->logger->playback(),
            ];
        } catch (\Exception $e) {
            $responseContent = [
                'success' => false,
                'error_message' => $e->getMessage(),
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
