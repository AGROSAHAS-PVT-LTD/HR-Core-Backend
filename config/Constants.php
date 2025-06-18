<?php

class Constants
{

  public const DateTimeFormat = 'd-m-Y h:i A';

  //Constants::DateTimeFormat
  public const DateFormat = 'd-m-Y';
  public const TimeFormat = 'h:i A';
  public const DateTimeHumanFormat = 'F j, Y, g:i a';
  public const DateTimeHumanFormatShort = 'M d, Y H:i';
  public const BaseFolderVisitImages = 'uploads/visitimages/';

  public const BaseFolderTaskUpdateFiles = 'uploads/taskupdatefiles/';

  public const BaseFolderLeaveRequestDocument = 'uploads/leaverequestdocuments/';

  public const BaseFolderExpenseProofs = 'uploads/expenseProofs/';

  public const BaseFolderUserDocumentRequest = 'uploads/userDocumentRequests/';

  public const BaseFolderEmployeeProfile = 'uploads/employeeProfilePictures';


  public const BaseFolderEmployeeProfileWithSlash = 'uploads/employeeProfilePictures/';

  public const BaseFolderEmployeeDocument = 'uploads/employeeDocuments/';

  public const BaseFolderChatFiles = 'uploads/chatFiles/';

  public const ALL_ADDONS_PURCHASE_LINK = 'https://czappstudio.com/laravel-addons/';
  public const All_ADDONS_ARRAY = [
    'BreakSystem' => [
      'name' => 'Break System',
      'description' => 'This module is used to manage breaks for employees.',
      'purchase_link' => 'https://czappstudio.com/product/break-system-addon-laravel/',
    ],
    'DataImportExport' => [
      'name' => 'Data Import Export',
      'description' => 'This module is used to import and export data from the system.',
      'purchase_link' => 'https://czappstudio.com/product/data-import-export-addon/',
    ],
    'DocumentManagement' => [
      'name' => 'Document Management',
      'description' => 'This module is used to manage document request for employees.',
      'purchase_link' => 'https://czappstudio.com/product/document-request-addon-laravel/',
    ],
    'DynamicForms' => [
      'name' => 'Dynamic Forms',
      'description' => 'This module is used to create dynamic forms.',
      'purchase_link' => 'https://czappstudio.com/product/custom-form-addon-laravel/',
    ],
    'GeofenceSystem' => [
      'name' => 'Geofence System',
      'description' => 'This module is used to manage geofence for employees.',
      'purchase_link' => 'https://czappstudio.com/product/geofence-attendance-addon-laravel/',
    ],
    'IpAddressAttendance' => [
      'name' => 'IP Address Attendance',
      'description' => 'This module is used to manage attendance based on IP Address.',
      'purchase_link' => 'https://czappstudio.com/product/ip-based-attendance-addon-laravel/',
    ],
    'LoanManagement' => [
      'name' => 'Loan Management',
      'description' => 'This module is used to manage loans for employees.',
      'purchase_link' => 'https://czappstudio.com/product/loan-request-addon-laravel/',
    ],
    /* 'ManagerApp' => [
       'name' => 'Manager App',
       'description' => 'This module is used to manage employees using a mobile app.',
       'purchase_link' => 'https://czappstudio.com/product/manager-app-field-manager-flutter/',
     ],*/
    'NoticeBoard' => [
      'name' => 'Notice Board',
      'description' => 'This module is used to manage notice board for employees.',
      'purchase_link' => 'https://czappstudio.com/product/notice-board-addon-laravel/',
    ],
    'OfflineTracking' => [
      'name' => 'Offline Tracking',
      'description' => 'This module is used to track employees offline.',
      'purchase_link' => 'https://czappstudio.com/product/offline-tracking-addon-laravel/',
    ],
    'PaymentCollection' => [
      'name' => 'Payment Collection',
      'description' => 'This module is used to collect payments from customers.',
      'purchase_link' => 'https://czappstudio.com/product/payment-collection-addon-laravel/',
    ],
    'ProductOrder' => [
      'name' => 'Product Order',
      'description' => 'This module is used to manage product orders.',
      'purchase_link' => 'https://czappstudio.com/product/product-order-system-addon-laravel/',
    ],
    'QRAttendance' => [
      'name' => 'QR Attendance',
      'description' => 'This module is used to manage attendance using QR Code.',
      'purchase_link' => 'https://czappstudio.com/product/qr-attendance-addon-laravel/',
    ],
    'SiteAttendance' => [
      'name' => 'Site Attendance',
      'description' => 'This module is used to manage attendance based on site.',
      'purchase_link' => 'https://czappstudio.com/product/site-attendance-addon-laravel/',
    ],
    'TaskSystem' => [
      'name' => 'Task System',
      'description' => 'This module is used to manage tasks for employees.',
      'purchase_link' => 'https://czappstudio.com/product/task-system-addon-laravel/',
    ],
    'UidLogin' => [
      'name' => 'One Tap Login',
      'description' => 'This module is used to login using UID.',
      'purchase_link' => 'https://czappstudio.com/product/uid-login-addon-laravel/',
    ],
    'AiChat' => [
      'name' => 'AI Business Assistant',
      'description' => 'This module is used to chat with AI.',
      'purchase_link' => '#',
    ],
    'DigitalIdCard' => [
      'name' => 'Digital ID Card',
      'description' => 'This module is used to manage digital ID cards for employees.',
      'purchase_link' => 'https://czappstudio.com/product/digital-id-card-addon/',
    ],
    'DynamicQrAttendance' => [
      'name' => 'Dynamic QR Attendance',
      'description' => 'This module is used to manage attendance using dynamic QR Code.',
      'purchase_link' => 'https://czappstudio.com/product/dynamic-qr-attendance-addon/',
    ],
    'Payroll' => [
      'name' => 'Payroll Management',
      'description' => 'This module is used to manage payroll for employees.',
      'purchase_link' => 'https://czappstudio.com/product/payroll-management-addon/',
    ],
    'SalesTarget' => [
      'name' => 'Sales Target',
      'description' => 'This module is used to manage sales targets for employees.',
      'purchase_link' => 'https://czappstudio.com/product/sales-target-addon/',
    ],
  ];
  public const BuiltInRoles = ['admin', 'hr', 'field_employee', 'office_employee', 'manager'];

}
