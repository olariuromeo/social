<?php

namespace ChargeBee\ChargeBee\Models;

use ChargeBee\ChargeBee\Model;

class InvoiceEstimateLineItem extends Model
{
  protected $allowed = [
    'id',
    'subscriptionId',
    'dateFrom',
    'dateTo',
    'unitAmount',
    'quantity',
    'amount',
    'pricingModel',
    'isTaxed',
    'taxAmount',
    'taxRate',
    'unitAmountInDecimal',
    'quantityInDecimal',
    'amountInDecimal',
    'discountAmount',
    'itemLevelDiscountAmount',
    'usagePercentage',
    'referenceLineItemId',
    'description',
    'entityDescription',
    'entityType',
    'taxExemptReason',
    'entityId',
    'customerId',
  ];

}

?>