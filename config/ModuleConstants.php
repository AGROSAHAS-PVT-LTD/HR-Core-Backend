<?php

class ModuleConstants
{
  const BREAK = 'BreakSystem';
  const DOCUMENT = 'DocumentManagement';
  const DATA_IMPORT_EXPORT = 'DataImportExport';
  const DYNAMIC_FORMS = 'DynamicForms';
  const GEOFENCE = 'GeofenceSystem';
  const IP_ADDRESS_ATTENDANCE = 'IpAddressAttendance';
  const LOAN_MANAGEMENT = 'LoanManagement';
  const MANAGER_APP = 'ManagerApp';
  const NOTICE_BOARD = 'NoticeBoard';
  const OFFLINE_TRACKING = 'OfflineTracking';
  const PAYMENT_COLLECTION = 'PaymentCollection';
  const PRODUCT_ORDER = 'ProductOrder';
  const QR_ATTENDANCE = 'QrAttendance';
  const SITE_ATTENDANCE = 'SiteAttendance';

  const AI_CHATBOT = 'AiChat';

  const DYNAMIC_QR_ATTENDANCE = 'DynamicQrAttendance';

  const TASK_SYSTEM = 'TaskSystem';
  const UID_LOGIN = 'UidLogin';

  const DIGITAL_ID_CARD = 'DigitalIdCard';

  const PAYROLL = 'Payroll';

  const SALES_TARGET = 'SalesTarget';


  //Build in Modules
  const LEAVE_MANAGEMENT = 'LeaveManagement';

  const EXPENSE_MANAGEMENT = 'ExpenseManagement';

  const CLIENT_VISIT = 'ClientVisit';

  const CHAT_SYSTEM = 'ChatSystem';

  const SOS = 'SoS';


  const STANDARD_MODULES = [
    self::LEAVE_MANAGEMENT,
    self::EXPENSE_MANAGEMENT,
    self::CLIENT_VISIT,
    self::CHAT_SYSTEM,
    self::SOS
  ];

  const ATTENDANCE_TYPES = [
    self::IP_ADDRESS_ATTENDANCE,
    self::QR_ATTENDANCE,
    self::DYNAMIC_QR_ATTENDANCE,
    self::GEOFENCE,
    self::SITE_ATTENDANCE
  ];

  const ALL_MODULES_EXCEPT_ATTENDANCE = [
    self::BREAK,
    self::DOCUMENT,
    self::DATA_IMPORT_EXPORT,
    self::DYNAMIC_FORMS,
    self::LOAN_MANAGEMENT,
    self::MANAGER_APP,
    self::NOTICE_BOARD,
    self::OFFLINE_TRACKING,
    self::PAYMENT_COLLECTION,
    self::PRODUCT_ORDER,
    self::TASK_SYSTEM,
    self::UID_LOGIN,
    self::DIGITAL_ID_CARD
  ];

  const All_MODULES = [
    self::BREAK,
    self::DOCUMENT,
    self::DATA_IMPORT_EXPORT,
    self::DYNAMIC_FORMS,
    self::GEOFENCE,
    self::IP_ADDRESS_ATTENDANCE,
    self::LOAN_MANAGEMENT,
    self::MANAGER_APP,
    self::NOTICE_BOARD,
    self::OFFLINE_TRACKING,
    self::PAYMENT_COLLECTION,
    self::PRODUCT_ORDER,
    self::QR_ATTENDANCE,
    self::SITE_ATTENDANCE,
    self::TASK_SYSTEM,
    self::UID_LOGIN,
    self::DIGITAL_ID_CARD,
    self::LEAVE_MANAGEMENT,
    self::EXPENSE_MANAGEMENT,
    self::CLIENT_VISIT,
    self::CHAT_SYSTEM
  ];

}
