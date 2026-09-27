<?php

return [
    'version' => '1.0.0',

    'inventory' => [
        'lot_method' => env('INVENTORY_LOT_METHOD', 'FEFO'),
        'expiry_alert_days' => (int) env('INVENTORY_EXPIRY_ALERT_DAYS', 30),
        'low_stock_threshold' => (int) env('INVENTORY_LOW_STOCK_THRESHOLD', 10),
        'audit_log_enabled' => (bool) env('INVENTORY_AUDIT_LOG_ENABLED', true),
    ],

    'qc' => [
        'auto_approve' => (bool) env('QC_AUTO_APPROVE', false),
        'approval_workflow' => (bool) env('QC_APPROVAL_WORKFLOW', true),
    ],

    'production' => [
        'batch_enabled' => (bool) env('PRODUCTION_BATCH_ENABLED', true),
        'lot_tracking' => (bool) env('PRODUCTION_LOT_TRACKING', true),
    ],

    'distribution' => [
        'auto_allocate' => (bool) env('DISTRIBUTION_AUTO_ALLOCATE', true),
    ],

    'financial' => [
        'currency' => env('FINANCIAL_CURRENCY', 'IDR'),
        'rounding' => (int) env('FINANCIAL_ROUNDING', 2),
        'costing_method' => env('FINANCIAL_COSTING_METHOD', 'AVG'),
    ],

    'statuses' => [
        'pr' => ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED', 'ORDERED', 'PARTIAL', 'COMPLETED', 'CANCELLED'],
        'po' => ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED', 'PARTIAL', 'COMPLETED', 'CANCELLED'],
        'gr' => ['DRAFT', 'RECEIVED', 'PARTIAL', 'COMPLETED', 'REJECTED'],
        'production' => ['PLANNED', 'RELEASED', 'IN_PROGRESS', 'PARTIAL', 'COMPLETED', 'CANCELLED'],
        'qc' => ['PENDING', 'PASSED', 'FAILED', 'CONDITIONAL'],
        'delivery' => ['PLANNED', 'PACKED', 'IN_TRANSIT', 'DELIVERED', 'PARTIAL', 'FAILED', 'RETURNED'],
        'opname' => ['DRAFT', 'COUNTED', 'APPROVED', 'POSTED'],
        'general' => ['DRAFT', 'ACTIVE', 'INACTIVE'],
    ],

    'movement_types' => [
        'PURCHASE_RECEIPT', 'STOCK_IN', 'STOCK_OUT',
        'PRODUCTION_CONSUMPTION', 'PRODUCTION_OUTPUT',
        'TRANSFER', 'ADJUSTMENT', 'STOCK_OPNAME',
        'WASTE', 'DELIVERY', 'RETURN',
    ],
];
