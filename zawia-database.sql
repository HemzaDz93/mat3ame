
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `result` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  KEY `activity_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `activity_logs_action_index` (`action`),
  KEY `activity_logs_created_at_index` (`created_at`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 08:27:55'),(2,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 08:28:11'),(3,1,'backup.failed','App\\Models\\BackupLog',1,'فشل إنشاء نسخة احتياطية: mysqldump.exe: Got error: 2004: \"Can\'t create TCP/IP socket (10106)\" when trying to connect',NULL,NULL,'failed','127.0.0.1','2026-10-18 08:28:21'),(4,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 08:28:57'),(5,1,'backup.failed','App\\Models\\BackupLog',2,'فشل إنشاء نسخة احتياطية: mysqldump.exe: Got error: 2004: \"Can\'t create TCP/IP socket (10106)\" when trying to connect',NULL,NULL,'failed','127.0.0.1','2026-10-18 08:28:57'),(6,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 08:29:04'),(7,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 08:29:41'),(8,1,'backup.created','App\\Models\\BackupLog',3,'إنشاء نسخة احتياطية: zawia-20261018-092942.sql',NULL,NULL,'success','127.0.0.1','2026-10-18 08:29:42'),(9,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 08:29:49'),(10,1,'backup.created','App\\Models\\BackupLog',4,'إنشاء نسخة احتياطية: zawia-20261018-092949.sql',NULL,NULL,'success','127.0.0.1','2026-10-18 08:29:49'),(11,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 09:35:48'),(12,NULL,'auth.failed',NULL,NULL,'محاولة دخول فاشلة باسم المستخدم: admin',NULL,NULL,'failure','127.0.0.1','2026-10-18 10:06:33'),(13,NULL,'auth.failed',NULL,NULL,'محاولة دخول فاشلة باسم المستخدم: admin',NULL,NULL,'failure','127.0.0.1','2026-10-18 10:06:39'),(14,NULL,'auth.failed',NULL,NULL,'محاولة دخول فاشلة باسم المستخدم: admin',NULL,NULL,'failure','127.0.0.1','2026-10-18 10:06:44'),(15,NULL,'auth.failed',NULL,NULL,'محاولة دخول فاشلة باسم المستخدم: admin',NULL,NULL,'failure','127.0.0.1','2026-10-18 10:06:53'),(16,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 10:07:04'),(17,NULL,'auth.failed',NULL,NULL,'محاولة دخول فاشلة باسم المستخدم: admin',NULL,NULL,'failure','127.0.0.1','2026-10-18 10:07:22'),(18,NULL,'auth.failed',NULL,NULL,'محاولة دخول فاشلة باسم المستخدم: admin',NULL,NULL,'failure','127.0.0.1','2026-10-18 10:07:29'),(19,NULL,'auth.failed',NULL,NULL,'محاولة دخول فاشلة باسم المستخدم: admin',NULL,NULL,'failure','127.0.0.1','2026-10-18 10:07:50'),(20,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 10:08:01'),(21,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 10:49:02'),(22,1,'employee.created','App\\Models\\Employee',1,'إضافة موظف: بن سليليح المسعود',NULL,'{\"emp_no\":\"EMP-001\",\"last_name\":\"\\u0628\\u0646 \\u0633\\u0644\\u064a\\u0644\\u064a\\u062d\",\"first_name\":\"\\u0627\\u0644\\u0645\\u0633\\u0639\\u0648\\u062f\",\"rank\":\"\\u062a\\u0642\\u0646\\u064a \\u0633\\u0627\\u0645\\u064a \\u0641\\u064a \\u0627\\u0644\\u0627\\u0639\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0627\\u0644\\u064a\"}','success','127.0.0.1','2026-10-18 11:01:07'),(23,1,'cards.printed',NULL,NULL,'طباعة بطاقات موظفين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:01:20'),(24,1,'cards.printed',NULL,NULL,'طباعة بطاقات متربصين: 25 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:03:48'),(25,1,'import.completed','App\\Models\\ImportLog',2,'استيراد ملف: بطاقات المطعم.xlsm',NULL,'{\"total\":57,\"created\":0,\"updated\":57,\"restored\":0,\"rejected\":0,\"skipped\":0}','success','127.0.0.1','2026-10-18 11:04:38'),(26,1,'cards.printed',NULL,NULL,'طباعة بطاقات متربصين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:04:53'),(27,1,'cards.printed',NULL,NULL,'طباعة بطاقات متربصين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:07:38'),(28,1,'cards.printed',NULL,NULL,'طباعة بطاقات متربصين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:15:07'),(29,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:28:24'),(30,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:28:57'),(31,1,'cards.printed',NULL,NULL,'طباعة بطاقات موظفين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:29:07'),(32,1,'cards.printed',NULL,NULL,'طباعة بطاقات متربصين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:29:25'),(33,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:30:11'),(34,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:30:23'),(35,1,'cards.printed',NULL,NULL,'طباعة بطاقات متربصين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:51:38'),(36,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 11:56:57'),(37,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 11:57:12'),(38,1,'cards.printed',NULL,NULL,'طباعة بطاقات موظفين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:57:13'),(39,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-18 11:57:33'),(40,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','127.0.0.1','2026-10-18 11:57:34'),(41,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','127.0.0.1','2026-10-18 12:03:43'),(42,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','127.0.0.1','2026-10-18 12:20:07'),(43,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','127.0.0.1','2026-10-18 12:29:09'),(44,1,'cards.printed',NULL,NULL,'طباعة بطاقات موظفين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 12:39:05'),(45,1,'cards.printed',NULL,NULL,'طباعة بطاقات متربصين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 12:40:04'),(46,1,'cards.printed',NULL,NULL,'طباعة بطاقات متربصين: 25 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 12:40:09'),(47,1,'cards.printed',NULL,NULL,'طباعة بطاقات موظفين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-18 12:40:46'),(48,1,'employee_meal.served','App\\Models\\Employee',1,'تسليم وجبة موظف',NULL,NULL,'success','127.0.0.1','2026-10-18 12:42:10'),(49,1,'meal_forecast.saved','App\\Models\\MealForecast',1,'إعلان العدد المتوقّع لوجبات المتربصين بتاريخ 2026-10-18: 60',NULL,'{\"expected_count\":60,\"note\":null}','success','127.0.0.1','2026-10-18 13:35:13'),(50,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-19 06:41:27'),(51,1,'user.created','App\\Models\\User',2,'إنشاء مستخدم: LOTFI',NULL,'{\"name\":\"\\u0627\\u0644\\u0634\\u0627\\u0647\\u062f \\u0645\\u062d\\u0645\\u062f \\u0644\\u0637\\u0641\\u064a\",\"username\":\"LOTFI\",\"role\":\"assistant\",\"is_active\":true}','success','127.0.0.1','2026-10-19 06:44:51'),(52,2,'auth.login','App\\Models\\User',2,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-19 06:45:15'),(53,2,'meal_forecast.saved','App\\Models\\MealForecast',2,'إعلان العدد المتوقّع لوجبات المتربصين بتاريخ 2026-10-19: 50',NULL,'{\"expected_count\":50,\"note\":null}','success','127.0.0.1','2026-10-19 06:45:37'),(54,2,'auth.logout','App\\Models\\User',2,'تسجيل خروج',NULL,NULL,'success','127.0.0.1','2026-10-19 06:45:54'),(55,1,'cards.printed',NULL,NULL,'طباعة بطاقات موظفين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-19 06:51:44'),(56,1,'cards.printed',NULL,NULL,'طباعة بطاقات متربصين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-19 06:56:57'),(57,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','127.0.0.1','2026-10-19 06:57:47'),(58,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','127.0.0.1','2026-10-19 06:58:15'),(59,1,'cards.printed',NULL,NULL,'طباعة بطاقات متربصين: 25 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-19 06:58:28'),(60,1,'cards.printed',NULL,NULL,'طباعة بطاقات موظفين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-19 06:59:01'),(61,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','127.0.0.1','2026-10-19 07:00:17'),(62,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','127.0.0.1','2026-10-19 07:00:42'),(63,1,'payment.created_approved','App\\Models\\Payment',1,'تسجيل سداد (شهر واحد) بمبلغ 330.00',NULL,'{\"trainee_id\":29,\"plan\":\"month\",\"start_date\":\"2026-10-19T00:00:00.000000Z\",\"end_date\":\"2026-11-18T00:00:00.000000Z\",\"amount\":\"330.00\",\"status\":\"approved\"}','success','127.0.0.1','2026-10-19 07:29:37'),(64,1,'meal.served','App\\Models\\Trainee',29,'تسليم وجبة متربص',NULL,NULL,'success','127.0.0.1','2026-10-19 07:29:45'),(65,2,'auth.login','App\\Models\\User',2,'تسجيل دخول ناجح',NULL,NULL,'success','127.0.0.1','2026-10-19 07:30:29'),(66,1,'payment.created_approved','App\\Models\\Payment',2,'تسجيل سداد (يوم واحد) بمبلغ 15.00',NULL,'{\"trainee_id\":5,\"plan\":\"day\",\"start_date\":\"2026-10-19T00:00:00.000000Z\",\"end_date\":\"2026-10-19T00:00:00.000000Z\",\"amount\":\"15.00\",\"status\":\"approved\"}','success','127.0.0.1','2026-10-19 07:31:14'),(67,1,'meal.served','App\\Models\\Trainee',5,'تسليم وجبة متربص',NULL,NULL,'success','127.0.0.1','2026-10-19 07:31:16'),(68,1,'cards.printed',NULL,NULL,'طباعة بطاقات موظفين: 1 بطاقة',NULL,NULL,'success','127.0.0.1','2026-10-19 07:31:44'),(69,1,'employee_meal.served','App\\Models\\Employee',1,'تسليم وجبة موظف',NULL,NULL,'success','127.0.0.1','2026-10-19 07:34:50'),(70,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','192.168.1.18','2026-10-19 08:09:20'),(71,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','192.168.1.18','2026-10-19 08:21:34'),(72,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','192.168.1.14','2026-10-19 08:27:41'),(73,1,'settings.updated',NULL,NULL,'تحديث إعدادات المؤسسة',NULL,NULL,'success','192.168.1.18','2026-10-19 08:27:55'),(74,1,'auth.logout','App\\Models\\User',1,'تسجيل خروج',NULL,NULL,'success','192.168.1.14','2026-10-19 08:28:06'),(75,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','192.168.1.14','2026-10-19 08:28:17'),(76,1,'auth.login','App\\Models\\User',1,'تسجيل دخول ناجح',NULL,NULL,'success','192.168.1.18','2026-10-19 08:52:00');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `app_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `level` varchar(255) NOT NULL DEFAULT 'info',
  `audience` varchar(255) NOT NULL DEFAULT 'all',
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `read_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_notifications_user_id_read_at_index` (`user_id`,`read_at`),
  CONSTRAINT `app_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `app_notifications` WRITE;
/*!40000 ALTER TABLE `app_notifications` DISABLE KEYS */;
INSERT INTO `app_notifications` VALUES (1,'import','اكتمل استيراد ملف المتربصين','الملف بطاقات المطعم.xlsm: إنشاء 0، تحديث 57، استعادة 0، رفض 0.','success','admin,staff','{\"import_log_id\":2}','2026-10-18 12:03:46',1,'2026-10-18 11:04:38','2026-10-18 12:03:46');
/*!40000 ALTER TABLE `app_notifications` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `backup_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT 0,
  `status` enum('success','failed') NOT NULL DEFAULT 'success',
  `note` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backup_logs_created_by_foreign` (`created_by`),
  CONSTRAINT `backup_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `backup_logs` WRITE;
/*!40000 ALTER TABLE `backup_logs` DISABLE KEYS */;
INSERT INTO `backup_logs` VALUES (3,'zawia-20261018-092942.sql',22401,'success','اختبار آلي 2',1,'2026-10-18 08:29:42'),(4,'zawia-20261018-092949.sql',23591,'success','اختبار آلي 3',1,'2026-10-18 08:29:49');
/*!40000 ALTER TABLE `backup_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trainee_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_uploaded_by_foreign` (`uploaded_by`),
  KEY `documents_trainee_id_type_index` (`trainee_id`,`type`),
  CONSTRAINT `documents_trainee_id_foreign` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
INSERT INTO `documents` VALUES (1,1,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_c95632fa-38a2-4bd0-a3b0-cf46a707869a.pdf','certificat_medical_36_c95632fa-38a2-4bd0-a3b0-cf46a707869a.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(2,1,'school','takwindz_files/certscol_path/certscol_36_022e6253-5f11-4c97-a098-fdf8cd2e7d3e.pdf','certscol_36_022e6253-5f11-4c97-a098-fdf8cd2e7d3e.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(3,2,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_7a8eb4a9-7534-407f-b703-48cbf76c9889.pdf','certificat_medical_36_7a8eb4a9-7534-407f-b703-48cbf76c9889.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(4,2,'school','takwindz_files/certscol_path/certscol_36_b4ee3a59-3f10-4be5-b0a8-a56abce0f6bc.pdf','certscol_36_b4ee3a59-3f10-4be5-b0a8-a56abce0f6bc.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(5,3,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_bf9fe5f0-dc3f-4aa6-82a9-13bce193f6df.pdf','certificat_medical_36_bf9fe5f0-dc3f-4aa6-82a9-13bce193f6df.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(6,4,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_b2f89435-b91a-4168-b56d-299ec3228cc9.pdf','certificat_medical_36_b2f89435-b91a-4168-b56d-299ec3228cc9.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(7,4,'school','takwindz_files/certscol_path/certscol_36_24c204ed-c21e-41f6-be00-415a4d06b58c.pdf','certscol_36_24c204ed-c21e-41f6-be00-415a4d06b58c.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(8,8,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_88fe1728-3c89-42ed-b513-3f0043603d54.pdf','certificat_medical_36_88fe1728-3c89-42ed-b513-3f0043603d54.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(9,8,'school','takwindz_files/certscol_path/certscol_36_35a95f74-9faf-4c0c-98de-aeee32e17821.pdf','certscol_36_35a95f74-9faf-4c0c-98de-aeee32e17821.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(10,9,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_fa2ce4a7-36ee-4948-898f-69a63fa29a27.pdf','certificat_medical_36_fa2ce4a7-36ee-4948-898f-69a63fa29a27.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(11,9,'school','takwindz_files/certscol_path/certscol_36_a32b05a3-27ae-4599-82eb-06e2c36d02f8.pdf','certscol_36_a32b05a3-27ae-4599-82eb-06e2c36d02f8.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(12,11,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_85d68754-0082-4358-a78a-a195a993ef69.pdf','certificat_medical_36_85d68754-0082-4358-a78a-a195a993ef69.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(13,11,'school','takwindz_files/certscol_path/certscol_36_0fca36d0-4e4a-4d4b-8fe4-c2c4bcbfde58.pdf','certscol_36_0fca36d0-4e4a-4d4b-8fe4-c2c4bcbfde58.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(14,12,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_1bcc9ac5-e017-4e7e-a986-b1c9192a1f4b.pdf','certificat_medical_36_1bcc9ac5-e017-4e7e-a986-b1c9192a1f4b.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(15,12,'school','takwindz_files/certscol_path/certscol_36_ae0a5e03-d4f6-401f-8dad-7630290d457b.pdf','certscol_36_ae0a5e03-d4f6-401f-8dad-7630290d457b.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(16,13,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_d69f5a7e-781e-4451-ae24-ad391f66f2e3.pdf','certificat_medical_36_d69f5a7e-781e-4451-ae24-ad391f66f2e3.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(17,13,'school','takwindz_files/certscol_path/certscol_36_7cdff17d-81e5-48e0-a264-a2a04bb37c67.pdf','certscol_36_7cdff17d-81e5-48e0-a264-a2a04bb37c67.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(18,14,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_47e1cf4e-2426-4218-953f-8907e6a29ffe.pdf','certificat_medical_36_47e1cf4e-2426-4218-953f-8907e6a29ffe.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(19,14,'school','takwindz_files/certscol_path/certscol_36_13d8b24b-4423-4ced-af68-38ebe40e5e7d.pdf','certscol_36_13d8b24b-4423-4ced-af68-38ebe40e5e7d.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(20,15,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_f461ece4-6d5e-4c1f-87aa-8e6a6fb4ef44.pdf','certificat_medical_36_f461ece4-6d5e-4c1f-87aa-8e6a6fb4ef44.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(21,15,'school','takwindz_files/certscol_path/certscol_36_3a5e76fe-afb8-4788-a2ba-42d5a3017b74.pdf','certscol_36_3a5e76fe-afb8-4788-a2ba-42d5a3017b74.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(22,16,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_6027299c-e8b3-4d14-87c6-55c52e4e0d19.pdf','certificat_medical_36_6027299c-e8b3-4d14-87c6-55c52e4e0d19.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(23,16,'school','takwindz_files/certscol_path/certscol_36_9f2ac789-3923-47f1-987d-7dbbbe4db5bf.pdf','certscol_36_9f2ac789-3923-47f1-987d-7dbbbe4db5bf.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(24,19,'school','takwindz_files/certscol_path/certscol_36_9625a257-c1c3-4998-ad97-851afd71d348.pdf','certscol_36_9625a257-c1c3-4998-ad97-851afd71d348.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(25,20,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_daa63d89-9ded-40ea-b6c2-abf915928ba2.pdf','certificat_medical_36_daa63d89-9ded-40ea-b6c2-abf915928ba2.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(26,20,'school','takwindz_files/certscol_path/certscol_36_d2994a51-3bea-4d68-b2ca-cb531c488077.pdf','certscol_36_d2994a51-3bea-4d68-b2ca-cb531c488077.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(27,21,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_ad030fa7-169c-4687-ac22-b19825ac92c7.pdf','certificat_medical_36_ad030fa7-169c-4687-ac22-b19825ac92c7.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(28,21,'school','takwindz_files/certscol_path/certscol_36_2f31979f-788c-4bb1-8cb7-72a8778bb920.pdf','certscol_36_2f31979f-788c-4bb1-8cb7-72a8778bb920.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(29,22,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_939699f2-8cd3-49c0-9328-6115d26d6284.pdf','certificat_medical_36_939699f2-8cd3-49c0-9328-6115d26d6284.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(30,22,'school','takwindz_files/certscol_path/certscol_36_2a1a64cb-993e-46ab-84f2-a6ea8a96b1fd.pdf','certscol_36_2a1a64cb-993e-46ab-84f2-a6ea8a96b1fd.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(31,24,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_8a99d5df-80b9-4d24-9952-459af786f655.pdf','certificat_medical_36_8a99d5df-80b9-4d24-9952-459af786f655.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(32,24,'school','takwindz_files/certscol_path/certscol_36_5e6dd018-667e-45a3-b332-65bde0551051.pdf','certscol_36_5e6dd018-667e-45a3-b332-65bde0551051.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(33,25,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_74565c14-cef3-4964-883e-35b312216a64.pdf','certificat_medical_36_74565c14-cef3-4964-883e-35b312216a64.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(34,25,'school','takwindz_files/certscol_path/certscol_36_06b5805f-ee5c-4937-9f29-2a818bc44788.pdf','certscol_36_06b5805f-ee5c-4937-9f29-2a818bc44788.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(35,26,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_a2094774-cd90-4ab7-b949-078626eff7f6.pdf','certificat_medical_36_a2094774-cd90-4ab7-b949-078626eff7f6.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(36,26,'school','takwindz_files/certscol_path/certscol_36_2e1629fb-469f-4a99-b83e-4637eb7117af.pdf','certscol_36_2e1629fb-469f-4a99-b83e-4637eb7117af.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(37,28,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_2d3c5b3b-16b8-457b-bf77-a2f69c414b6e.pdf','certificat_medical_36_2d3c5b3b-16b8-457b-bf77-a2f69c414b6e.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(38,28,'school','takwindz_files/certscol_path/certscol_36_76be4dfd-c71f-46af-b2f0-ae31067d89b5.pdf','certscol_36_76be4dfd-c71f-46af-b2f0-ae31067d89b5.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(39,29,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_0c8b499f-33c7-4020-b8e9-4daa23799a3c.pdf','certificat_medical_36_0c8b499f-33c7-4020-b8e9-4daa23799a3c.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(40,29,'school','takwindz_files/certscol_path/certscol_36_19d2e3f8-fce0-4110-9fac-f6e289a172d5.pdf','certscol_36_19d2e3f8-fce0-4110-9fac-f6e289a172d5.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(41,30,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_76d4863d-7c22-4829-840a-e8f00a819ca2.pdf','certificat_medical_36_76d4863d-7c22-4829-840a-e8f00a819ca2.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(42,30,'school','takwindz_files/certscol_path/certscol_36_1fd15411-fc26-468d-b72c-d4c4a454455d.pdf','certscol_36_1fd15411-fc26-468d-b72c-d4c4a454455d.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(43,32,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_ac791cb2-d8bc-4b30-bf5a-b4da3a1de173.pdf','certificat_medical_36_ac791cb2-d8bc-4b30-bf5a-b4da3a1de173.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(44,32,'school','takwindz_files/certscol_path/certscol_36_15dcad31-c1d0-4ea1-82ce-59bc1e062c10.pdf','certscol_36_15dcad31-c1d0-4ea1-82ce-59bc1e062c10.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(45,33,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_61304a4b-caa2-4134-8154-d998fc23e3b9.pdf','certificat_medical_36_61304a4b-caa2-4134-8154-d998fc23e3b9.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(46,34,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_8ec3722e-b7bd-45e4-8c3a-7689eaef33d7.pdf','certificat_medical_36_8ec3722e-b7bd-45e4-8c3a-7689eaef33d7.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(47,34,'school','takwindz_files/certscol_path/certscol_36_a666a13d-2f8c-44aa-b25d-25d4650056f0.pdf','certscol_36_a666a13d-2f8c-44aa-b25d-25d4650056f0.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(48,35,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_f057b730-a94a-441a-a678-8c04d02ec54e.pdf','certificat_medical_36_f057b730-a94a-441a-a678-8c04d02ec54e.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(49,35,'school','takwindz_files/certscol_path/certscol_36_50cd19ec-012b-4078-b55d-405fd696b868.pdf','certscol_36_50cd19ec-012b-4078-b55d-405fd696b868.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(50,36,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_3194d85d-504d-4761-a6cf-643140d1c471.pdf','certificat_medical_36_3194d85d-504d-4761-a6cf-643140d1c471.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(51,36,'school','takwindz_files/certscol_path/certscol_36_ce15066f-94c8-4972-a68d-73ed2b4dd571.pdf','certscol_36_ce15066f-94c8-4972-a68d-73ed2b4dd571.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(52,37,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_f2a227a2-7c15-4a0b-9747-66cc4a12f8c3.pdf','certificat_medical_36_f2a227a2-7c15-4a0b-9747-66cc4a12f8c3.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(53,37,'school','takwindz_files/certscol_path/certscol_36_9bf17a0c-1a57-4395-ba7b-b5483046ed85.pdf','certscol_36_9bf17a0c-1a57-4395-ba7b-b5483046ed85.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(54,41,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_601aa5a8-627c-4b61-971c-2a1422c1e03f.pdf','certificat_medical_36_601aa5a8-627c-4b61-971c-2a1422c1e03f.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(55,41,'school','takwindz_files/certscol_path/certscol_36_9259efaf-b8d0-4c76-bd91-2a0acf70e700.pdf','certscol_36_9259efaf-b8d0-4c76-bd91-2a0acf70e700.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(56,43,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_a421d6fe-e3ba-4748-8364-6c596d368f8a.pdf','certificat_medical_36_a421d6fe-e3ba-4748-8364-6c596d368f8a.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(57,43,'school','takwindz_files/certscol_path/certscol_36_ee1a2030-c5d8-4c12-b27b-6b80eb0b83af.pdf','certscol_36_ee1a2030-c5d8-4c12-b27b-6b80eb0b83af.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(58,44,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_9e477f9d-0b70-4b8b-9e5c-ba732fad6a66.pdf','certificat_medical_36_9e477f9d-0b70-4b8b-9e5c-ba732fad6a66.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(59,45,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_05cd06f9-87e4-474d-9b4b-2c5dc7fdc9c2.pdf','certificat_medical_36_05cd06f9-87e4-474d-9b4b-2c5dc7fdc9c2.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(60,45,'school','takwindz_files/certscol_path/certscol_36_6eb42a3f-6f77-4975-8caa-b1a2d01a3ccc.pdf','certscol_36_6eb42a3f-6f77-4975-8caa-b1a2d01a3ccc.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(61,46,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_d4493532-18e8-4b1b-90cf-67dec81e2379.pdf','certificat_medical_36_d4493532-18e8-4b1b-90cf-67dec81e2379.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(62,46,'school','takwindz_files/certscol_path/certscol_36_cf63f86a-db11-4939-a06c-4bf8d3f6b6c2.pdf','certscol_36_cf63f86a-db11-4939-a06c-4bf8d3f6b6c2.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(63,47,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_5a0ff823-4251-4681-b008-a044381d9cbc.pdf','certificat_medical_36_5a0ff823-4251-4681-b008-a044381d9cbc.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(64,47,'school','takwindz_files/certscol_path/certscol_36_e422ed15-e253-487f-978d-0e973ebae570.pdf','certscol_36_e422ed15-e253-487f-978d-0e973ebae570.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(65,48,'school','takwindz_files/certscol_path/certscol_36_86558de0-c00a-4d1e-adeb-d1f5f890299b.pdf','certscol_36_86558de0-c00a-4d1e-adeb-d1f5f890299b.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(66,50,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_65bf8699-d947-47a9-85c7-e27a3663ec15.pdf','certificat_medical_36_65bf8699-d947-47a9-85c7-e27a3663ec15.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(67,51,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_859f2978-016a-4859-9add-6d7cadd5f93b.pdf','certificat_medical_36_859f2978-016a-4859-9add-6d7cadd5f93b.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(68,51,'school','takwindz_files/certscol_path/certscol_36_86763471-9a96-45d8-855a-c4156dcac36e.pdf','certscol_36_86763471-9a96-45d8-855a-c4156dcac36e.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(69,52,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_81ef22d3-0ce5-4d22-9aa6-e720f1ff91bd.pdf','certificat_medical_36_81ef22d3-0ce5-4d22-9aa6-e720f1ff91bd.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(70,52,'school','takwindz_files/certscol_path/certscol_36_3f72391a-68be-4a9b-92a3-bab747e0ebfc.pdf','certscol_36_3f72391a-68be-4a9b-92a3-bab747e0ebfc.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(71,53,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_459af6d9-ddbe-4f0a-9f88-a6081435507d.pdf','certificat_medical_36_459af6d9-ddbe-4f0a-9f88-a6081435507d.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(72,53,'school','takwindz_files/certscol_path/certscol_36_95b19ed7-8959-49e8-ac0c-571ecbeaeb29.pdf','certscol_36_95b19ed7-8959-49e8-ac0c-571ecbeaeb29.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(73,54,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_ee636e07-d5d2-4df6-accf-33a7c267ca56.pdf','certificat_medical_36_ee636e07-d5d2-4df6-accf-33a7c267ca56.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(74,55,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_67bb702d-023a-4ea7-80c5-81ad7c7e4aa4.pdf','certificat_medical_36_67bb702d-023a-4ea7-80c5-81ad7c7e4aa4.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(75,55,'school','takwindz_files/certscol_path/certscol_36_319da07e-2c6f-4c65-93c9-788df4c1bb3c.pdf','certscol_36_319da07e-2c6f-4c65-93c9-788df4c1bb3c.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(76,56,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_29d40e59-ee0f-44b4-9230-8eac781cccc1.pdf','certificat_medical_36_29d40e59-ee0f-44b4-9230-8eac781cccc1.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(77,56,'school','takwindz_files/certscol_path/certscol_36_0586dd0b-3ade-4a44-b1dc-0f4810e92315.pdf','certscol_36_0586dd0b-3ade-4a44-b1dc-0f4810e92315.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(78,57,'medical','takwindz_files/certificat_medical_path/certificat_medical_36_c7571ded-5c84-4608-9406-bb4ef6b47f68.pdf','certificat_medical_36_c7571ded-5c84-4608-9406-bb4ef6b47f68.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(79,57,'school','takwindz_files/certscol_path/certscol_36_7cc85ffb-f6c7-4f5b-8a11-f98c82a84aeb.pdf','certscol_36_7cc85ffb-f6c7-4f5b-8a11-f98c82a84aeb.pdf',NULL,0,1,'2026-10-18 10:51:09','2026-10-18 10:51:09');
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `employee_meals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_meals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `day` date NOT NULL,
  `served_at` timestamp NULL DEFAULT NULL,
  `served_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_meals_employee_id_day_unique` (`employee_id`,`day`),
  KEY `employee_meals_served_by_foreign` (`served_by`),
  KEY `employee_meals_day_index` (`day`),
  CONSTRAINT `employee_meals_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_meals_served_by_foreign` FOREIGN KEY (`served_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `employee_meals` WRITE;
/*!40000 ALTER TABLE `employee_meals` DISABLE KEYS */;
INSERT INTO `employee_meals` VALUES (1,1,'2026-10-18','2026-10-18 12:42:10',1,'2026-10-18 12:42:10','2026-10-18 12:42:10'),(2,1,'2026-10-19','2026-10-19 07:34:50',1,'2026-10-19 07:34:50','2026-10-19 07:34:50');
/*!40000 ALTER TABLE `employee_meals` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `employee_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `plan` enum('day','month','quarter') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `requested_by` bigint(20) unsigned DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `note` text DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `expiry_notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_payments_requested_by_foreign` (`requested_by`),
  KEY `employee_payments_approved_by_foreign` (`approved_by`),
  KEY `employee_payments_employee_id_status_index` (`employee_id`,`status`),
  KEY `employee_payments_start_date_end_date_index` (`start_date`,`end_date`),
  KEY `employee_payments_created_at_index` (`created_at`),
  CONSTRAINT `employee_payments_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_payments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_payments_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `employee_payments` WRITE;
/*!40000 ALTER TABLE `employee_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_payments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `emp_no` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `rank` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_emp_no_unique` (`emp_no`),
  KEY `employees_last_name_index` (`last_name`),
  KEY `employees_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,'EMP-001','المسعود','بن سليليح','1995-06-23','مسعد ولاية الجلفة','2024-05-05','تقني سامي في الاعلام الالي','employees/photos/EMP-001-9rRaI4oS.jpg',NULL,NULL,'2026-10-18 11:01:07','2026-10-18 11:01:07');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `import_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `sheet` varchar(255) DEFAULT NULL,
  `total_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `accepted` int(10) unsigned NOT NULL DEFAULT 0,
  `updated` int(10) unsigned NOT NULL DEFAULT 0,
  `rejected` int(10) unsigned NOT NULL DEFAULT 0,
  `skipped` int(10) unsigned NOT NULL DEFAULT 0,
  `report` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report`)),
  `status` enum('success','failed','partial') NOT NULL DEFAULT 'success',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_logs_created_by_foreign` (`created_by`),
  CONSTRAINT `import_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `import_logs` WRITE;
/*!40000 ALTER TABLE `import_logs` DISABLE KEYS */;
INSERT INTO `import_logs` VALUES (2,'بطاقات المطعم.xlsm','MINASA',57,0,57,0,0,'{\"counts\":{\"total\":57,\"created\":0,\"updated\":57,\"restored\":0,\"rejected\":0,\"skipped\":0},\"rows\":[{\"row\":2,\"action\":\"update\",\"name\":\"\\u0628\\u0648\\u0634\\u0646\\u062f\\u0648\\u0642\\u0629 \\u062f\\u062d\\u0645\\u0627\\u0646\",\"reg_no\":\"2396\",\"reasons\":[]},{\"row\":3,\"action\":\"update\",\"name\":\"\\u0645\\u0647\\u062f\\u064a \\u0623\\u062d\\u0645\\u062f\",\"reg_no\":\"67099\",\"reasons\":[]},{\"row\":4,\"action\":\"update\",\"name\":\"\\u0628\\u0646 \\u0632\\u0627\\u0632\\u0647 \\u0639\\u0628\\u062f \\u0627\\u0644\\u0643\\u0631\\u064a\\u0645\",\"reg_no\":\"477133\",\"reasons\":[]},{\"row\":5,\"action\":\"update\",\"name\":\"\\u0633\\u0644\\u0641\\u0627\\u0648\\u064a \\u0639\\u0628\\u062f \\u0627\\u0644\\u062c\\u0628\\u0627\\u0631\",\"reg_no\":\"184194\",\"reasons\":[]},{\"row\":6,\"action\":\"update\",\"name\":\"\\u0628\\u0627\\u0644\\u062d\\u0633\\u064a\\u0646 \\u0639\\u062b\\u0645\\u0627\\u0646\",\"reg_no\":\"472920\",\"reasons\":[]},{\"row\":7,\"action\":\"update\",\"name\":\"\\u063a\\u064a\\u0644\\u0627\\u0646\\u064a \\u0645\\u062d\\u0645\\u062f \\u0634\\u0627\\u0643\\u0631 \\u0627\\u0644\\u0644\\u0647\",\"reg_no\":\"679413\",\"reasons\":[]},{\"row\":8,\"action\":\"update\",\"name\":\"\\u0628\\u0648\\u0643\\u0644\\u0628\\u0629 \\u0639\\u0628\\u062f  \\u0627\\u0644\\u0648\\u0627\\u062c\\u062f\",\"reg_no\":\"446305\",\"reasons\":[]},{\"row\":9,\"action\":\"update\",\"name\":\"\\u0628\\u0646 \\u0634\\u0646\\u0629 \\u062e\\u0627\\u0644\\u062f\",\"reg_no\":\"543466\",\"reasons\":[]},{\"row\":10,\"action\":\"update\",\"name\":\"\\u0639\\u0628\\u0639\\u0648\\u0628 \\u0646\\u0648\\u0631\\u0627\\u0644\\u062f\\u064a\\u0646\",\"reg_no\":\"726281\",\"reasons\":[]},{\"row\":11,\"action\":\"update\",\"name\":\"\\u0648\\u0642\\u0627\\u062f \\u0645\\u062d\\u0645\\u062f \\u0636\\u064a\\u0627\\u0621 \\u0627\\u0644\\u062f\\u064a\\u0646\",\"reg_no\":\"691398\",\"reasons\":[]},{\"row\":12,\"action\":\"update\",\"name\":\"\\u063a\\u0648\\u064a\\u0646\\u064a \\u0632\\u064a\\u0627\\u062f\",\"reg_no\":\"745448\",\"reasons\":[]},{\"row\":13,\"action\":\"update\",\"name\":\"\\u0637\\u0631\\u064a\\u0629 \\u0627\\u0633\\u062d\\u0627\\u0642\",\"reg_no\":\"257487\",\"reasons\":[]},{\"row\":14,\"action\":\"update\",\"name\":\"\\u0627\\u0644\\u062a\\u0647\\u0627\\u0645\\u064a \\u0645\\u0646\\u062a\\u0635\\u0631 \\u0628\\u0627\\u0644\\u0644\\u0647\",\"reg_no\":\"213307\",\"reasons\":[]},{\"row\":15,\"action\":\"update\",\"name\":\"\\u062c\\u0645\\u0644 \\u0635\\u0627\\u0628\\u0631\",\"reg_no\":\"164986\",\"reasons\":[]},{\"row\":16,\"action\":\"update\",\"name\":\"\\u062e\\u0631\\u0641\\u064a \\u0623\\u0628\\u0648\\u0628\\u0643\\u0631\",\"reg_no\":\"213484\",\"reasons\":[]},{\"row\":17,\"action\":\"update\",\"name\":\"\\u0628\\u0646 \\u0647\\u0646\\u064a\\u0629 \\u0633\\u064a\\u0641 \\u0627\\u0644\\u062f\\u064a\\u0646\",\"reg_no\":\"585261\",\"reasons\":[]},{\"row\":18,\"action\":\"update\",\"name\":\"\\u063a\\u064a\\u0627\\u0628\\u0647 \\u0623\\u0633\\u0627\\u0645\\u0629\",\"reg_no\":\"657347\",\"reasons\":[]},{\"row\":19,\"action\":\"update\",\"name\":\"\\u0645\\u0646\\u0627\\u0635\\u0631\\u064a\\u0629 \\u0635\\u0644\\u0627\\u062d \\u0627\\u0644\\u062f\\u064a\\u0646\",\"reg_no\":\"654626\",\"reasons\":[]},{\"row\":20,\"action\":\"update\",\"name\":\"\\u0628\\u0646 \\u0631\\u0645\\u0636\\u0627\\u0646\\u0647 \\u0645\\u0639\\u062a\\u0635\\u0645 \\u0628\\u0627\\u0644\\u0644\\u0647\",\"reg_no\":\"232854\",\"reasons\":[]},{\"row\":21,\"action\":\"update\",\"name\":\"\\u063a\\u0648\\u0644\\u0629 \\u0637\\u0644\\u062d\\u0629\",\"reg_no\":\"544196\",\"reasons\":[]},{\"row\":22,\"action\":\"update\",\"name\":\"\\u062d\\u0627\\u062c \\u0633\\u0639\\u064a\\u062f \\u0639\\u0628\\u062f \\u0627\\u0644\\u0643\\u0631\\u064a\\u0645\",\"reg_no\":\"317824\",\"reasons\":[]},{\"row\":23,\"action\":\"update\",\"name\":\"\\u0628\\u0648\\u0634\\u0646\\u062f\\u0648\\u0642\\u0629 \\u0628\\u0646 \\u0639\\u064a\\u0633\\u0649\",\"reg_no\":\"593914\",\"reasons\":[]},{\"row\":24,\"action\":\"update\",\"name\":\"\\u062f\\u0648\\u063a\\u0629 \\u0639\\u0628\\u062f \\u0627\\u0644\\u062d\\u0644\\u064a\\u0645\",\"reg_no\":\"511099\",\"reasons\":[]},{\"row\":25,\"action\":\"update\",\"name\":\"\\u0643\\u0631\\u064a\\u0643\\u0631 \\u0639\\u0628\\u062f \\u0627\\u0644\\u0646\\u0627\\u0641\\u0639\",\"reg_no\":\"420293\",\"reasons\":[]},{\"row\":26,\"action\":\"update\",\"name\":\"\\u062d\\u0645\\u0644\\u0627\\u0648\\u064a \\u0639\\u0628\\u062f \\u0627\\u0644\\u0646\\u0648\\u0631\",\"reg_no\":\"134732\",\"reasons\":[]},{\"row\":27,\"action\":\"update\",\"name\":\"\\u0628\\u0646 \\u0642\\u0648\\u064a\\u062f\\u0631 \\u0645\\u062d\\u062c\\u0648\\u0628\\u0629\",\"reg_no\":\"400237\",\"reasons\":[]},{\"row\":28,\"action\":\"update\",\"name\":\"\\u0628\\u0627\\u0644\\u0647\\u0627\\u0646\\u064a \\u063a\\u0632\\u0644\\u0627\\u0646\",\"reg_no\":\"657280\",\"reasons\":[]},{\"row\":29,\"action\":\"update\",\"name\":\"\\u063a\\u064a\\u0627\\u0637 \\u0633\\u0645\\u0631\",\"reg_no\":\"109417\",\"reasons\":[]},{\"row\":30,\"action\":\"update\",\"name\":\"\\u062e\\u064a\\u0627\\u0637 \\u0647\\u062f\\u064a\\u0644\",\"reg_no\":\"142476\",\"reasons\":[]},{\"row\":31,\"action\":\"update\",\"name\":\"\\u0635\\u0627\\u064a\\u0645 \\u0634\\u064a\\u0645\\u0627\\u0621\",\"reg_no\":\"565798\",\"reasons\":[]},{\"row\":32,\"action\":\"update\",\"name\":\"\\u0631\\u064a\\u063a\\u064a \\u0645\\u0627\\u0631\\u064a\\u0627\",\"reg_no\":\"365063\",\"reasons\":[]},{\"row\":33,\"action\":\"update\",\"name\":\"\\u0628\\u0627\\u0628\\u0627\\u0639\\u0631\\u0628\\u064a \\u0625\\u0632\\u062f\\u0647\\u0627\\u0631\",\"reg_no\":\"312641\",\"reasons\":[]},{\"row\":34,\"action\":\"update\",\"name\":\"\\u0627\\u0644\\u0639\\u0627\\u0628\\u062f \\u0633\\u062c\\u0648\\u062f\",\"reg_no\":\"321524\",\"reasons\":[]},{\"row\":35,\"action\":\"update\",\"name\":\"\\u063a\\u0648\\u064a\\u0646\\u064a \\u0633\\u0639\\u0627\\u062f\",\"reg_no\":\"341001\",\"reasons\":[]},{\"row\":36,\"action\":\"update\",\"name\":\"\\u0639\\u0631\\u0639\\u0627\\u0631 \\u0627\\u0645\\u064a\\u0646\\u0629\",\"reg_no\":\"566433\",\"reasons\":[]},{\"row\":37,\"action\":\"update\",\"name\":\"\\u0628\\u0648\\u0644\\u064a\\u0641\\u0629 \\u0641\\u0627\\u0637\\u0645\\u0647 \\u0627\\u0644\\u0632\\u0647\\u0631\\u0627\\u0621\",\"reg_no\":\"60393\",\"reasons\":[]},{\"row\":38,\"action\":\"update\",\"name\":\"\\u062f\\u0648\\u063a\\u0629 \\u0646\\u0648\\u0631 \\u0627\\u0644\\u0647\\u062f\\u0649\",\"reg_no\":\"723144\",\"reasons\":[]},{\"row\":39,\"action\":\"update\",\"name\":\"\\u0628\\u0648\\u0634\\u0646\\u062f\\u0648\\u0642\\u0629 \\u0645\\u0646\\u0627\\u0644\",\"reg_no\":\"699416\",\"reasons\":[]},{\"row\":40,\"action\":\"update\",\"name\":\"\\u0642\\u0645\\u0648 \\u0627\\u0644\\u0632\\u0647\\u0631\\u0629\",\"reg_no\":\"756385\",\"reasons\":[]},{\"row\":41,\"action\":\"update\",\"name\":\"\\u0628\\u0646 \\u0646\\u0648\\u0646\\u0647 \\u0633\\u0627\\u0631\\u0647\",\"reg_no\":\"245155\",\"reasons\":[]},{\"row\":42,\"action\":\"update\",\"name\":\"\\u0639\\u064a\\u0633\\u0627\\u0646\\u064a \\u0627\\u0646\\u0648\\u0627\\u0631\",\"reg_no\":\"386519\",\"reasons\":[]},{\"row\":43,\"action\":\"update\",\"name\":\"\\u0645\\u0632\\u0631\\u0642\\u0637 \\u0645\\u0627\\u0631\\u064a\\u0627\",\"reg_no\":\"652070\",\"reasons\":[]},{\"row\":44,\"action\":\"update\",\"name\":\"\\u0627\\u0644\\u0623\\u0639\\u0648\\u0631 \\u0639\\u0644\\u064a\",\"reg_no\":\"474400\",\"reasons\":[]},{\"row\":45,\"action\":\"update\",\"name\":\"\\u0627\\u0628\\u064a \\u0645\\u064a\\u0644\\u0648\\u062f \\u0645\\u062d\\u0645\\u062f \\u0646\\u0636\\u0627\\u0644\",\"reg_no\":\"741693\",\"reasons\":[]},{\"row\":46,\"action\":\"update\",\"name\":\"\\u0628\\u0648\\u0639\\u0644\\u064a \\u0633\\u064a\\u0641 \\u0627\\u0644\\u062f\\u064a\\u0646\",\"reg_no\":\"586688\",\"reasons\":[]},{\"row\":47,\"action\":\"update\",\"name\":\"\\u0628\\u0646 \\u0642\\u0648\\u064a\\u062f\\u0631 \\u062e\\u0627\\u0644\\u062f\",\"reg_no\":\"319920\",\"reasons\":[]},{\"row\":48,\"action\":\"update\",\"name\":\"\\u062f\\u0648\\u0645\\u0627\\u0646 \\u0639\\u0645\\u0627\\u062f\",\"reg_no\":\"63290\",\"reasons\":[]},{\"row\":49,\"action\":\"update\",\"name\":\"\\u0639\\u0632\\u064a\\u0632\\u064a \\u0644\\u0642\\u0645\\u0627\\u0646\",\"reg_no\":\"305028\",\"reasons\":[]},{\"row\":50,\"action\":\"update\",\"name\":\"\\u0645\\u062c\\u0648\\u062c\\u0629 \\u0639\\u0628\\u062f \\u0627\\u0644\\u0631\\u062d\\u0645\\u0627\\u0646\",\"reg_no\":\"690643\",\"reasons\":[]},{\"row\":51,\"action\":\"update\",\"name\":\"\\u0628\\u0644\\u0648\\u0632\\u064a \\u064a\\u0648\\u0633\\u0641\",\"reg_no\":\"623739\",\"reasons\":[]},{\"row\":52,\"action\":\"update\",\"name\":\"\\u0627\\u0645\\u0627\\u0646\\u064a \\u0645\\u062d\\u0645\\u062f \\u0627\\u0643\\u0631\\u0645\",\"reg_no\":\"622322\",\"reasons\":[]},{\"row\":53,\"action\":\"update\",\"name\":\"\\u0645\\u0642\\u0646\\u0634 \\u0639\\u0628\\u062f \\u0627\\u0644\\u0631\\u0632\\u0627\\u0642\",\"reg_no\":\"367222\",\"reasons\":[]},{\"row\":54,\"action\":\"update\",\"name\":\"\\u0646\\u0648\\u064a \\u0633\\u0644\\u064a\\u0645\",\"reg_no\":\"560296\",\"reasons\":[]},{\"row\":55,\"action\":\"update\",\"name\":\"\\u062d\\u0646\\u0648\\u0646\\u0647 \\u0639\\u0628\\u062f \\u0627\\u0644\\u062d\\u0642\",\"reg_no\":\"159294\",\"reasons\":[]},{\"row\":56,\"action\":\"update\",\"name\":\"\\u0628\\u0627\\u0644\\u0647\\u0627\\u0646\\u064a \\u0633\\u064a\\u0641 \\u0627\\u0644\\u062f\\u064a\\u0646\",\"reg_no\":\"337097\",\"reasons\":[]},{\"row\":57,\"action\":\"update\",\"name\":\"\\u0646\\u0648\\u064a \\u0645\\u062d\\u0645\\u062f\",\"reg_no\":\"404001\",\"reasons\":[]},{\"row\":58,\"action\":\"update\",\"name\":\"\\u0627\\u0644\\u0642\\u0646\\u064a \\u0645\\u062d\\u0645\\u062f \\u062d\\u0628\\u064a\\u0628 \\u0627\\u0644\\u0631\\u062d\\u0645\\u0627\\u0646\",\"reg_no\":\"477281\",\"reasons\":[]}]}','success',1,'2026-10-18 11:04:38','2026-10-18 11:04:38');
/*!40000 ALTER TABLE `import_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `meal_forecasts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meal_forecasts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `day` date NOT NULL,
  `expected_count` int(10) unsigned NOT NULL DEFAULT 0,
  `note` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `meal_forecasts_day_unique` (`day`),
  KEY `meal_forecasts_created_by_foreign` (`created_by`),
  KEY `meal_forecasts_updated_by_foreign` (`updated_by`),
  KEY `meal_forecasts_day_index` (`day`),
  CONSTRAINT `meal_forecasts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `meal_forecasts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `meal_forecasts` WRITE;
/*!40000 ALTER TABLE `meal_forecasts` DISABLE KEYS */;
INSERT INTO `meal_forecasts` VALUES (1,'2026-10-18',60,NULL,1,1,'2026-10-18 13:35:13','2026-10-18 13:35:13'),(2,'2026-10-19',50,NULL,2,2,'2026-10-19 06:45:37','2026-10-19 06:45:37');
/*!40000 ALTER TABLE `meal_forecasts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `meals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trainee_id` bigint(20) unsigned NOT NULL,
  `day` date NOT NULL,
  `served_at` timestamp NULL DEFAULT NULL,
  `served_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `meals_trainee_id_day_unique` (`trainee_id`,`day`),
  KEY `meals_served_by_foreign` (`served_by`),
  KEY `meals_day_index` (`day`),
  CONSTRAINT `meals_served_by_foreign` FOREIGN KEY (`served_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `meals_trainee_id_foreign` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `meals` WRITE;
/*!40000 ALTER TABLE `meals` DISABLE KEYS */;
INSERT INTO `meals` VALUES (1,29,'2026-10-19','2026-10-19 07:29:45',1,'2026-10-19 07:29:45','2026-10-19 07:29:45'),(2,5,'2026-10-19','2026-10-19 07:31:16',1,'2026-10-19 07:31:16','2026-10-19 07:31:16');
/*!40000 ALTER TABLE `meals` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_01_000001_create_settings_table',1),(5,'2026_01_01_000002_create_trainees_table',1),(6,'2026_01_01_000003_create_employees_table',1),(7,'2026_01_01_000004_create_documents_table',1),(8,'2026_01_01_000005_create_payments_table',1),(9,'2026_01_01_000006_create_meals_table',1),(10,'2026_01_01_000007_create_employee_meals_table',1),(11,'2026_01_01_000008_create_activity_logs_table',1),(12,'2026_01_01_000009_create_app_notifications_table',1),(13,'2026_01_01_000010_create_backup_logs_table',1),(14,'2026_01_01_000011_create_import_logs_table',1),(15,'2026_01_01_000012_add_unique_reg_no_to_trainees_table',2),(16,'2026_01_01_000013_create_employee_payments_table',3),(17,'2026_01_01_000014_add_assistant_role_to_users_table',4),(18,'2026_01_01_000015_create_meal_forecasts_table',4),(19,'2026_01_01_000016_add_expiry_notified_at_to_payments_tables',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `username` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trainee_id` bigint(20) unsigned NOT NULL,
  `plan` enum('day','month','quarter') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `requested_by` bigint(20) unsigned DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `note` text DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `expiry_notified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_requested_by_foreign` (`requested_by`),
  KEY `payments_approved_by_foreign` (`approved_by`),
  KEY `payments_trainee_id_status_index` (`trainee_id`,`status`),
  KEY `payments_start_date_end_date_index` (`start_date`,`end_date`),
  KEY `payments_created_at_index` (`created_at`),
  CONSTRAINT `payments_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_trainee_id_foreign` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,29,'month','2026-10-19','2026-11-18',330.00,'approved',1,1,NULL,'2026-10-19 07:29:37',NULL,'2026-10-19 07:29:37','2026-10-19 07:29:37'),(2,5,'day','2026-10-19','2026-10-19',15.00,'approved',1,1,'سداد وجبة يوم من منصة المطعم','2026-10-19 07:31:14',NULL,'2026-10-19 07:31:14','2026-10-19 07:31:14');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('backup_retention','10','2026-10-19 08:27:55'),('card_back_info_show','1','2026-10-19 08:27:55'),('card_back_note','','2026-10-19 08:27:55'),('card_back_note_show','0','2026-10-19 08:27:55'),('card_barcode_h','7','2026-10-19 08:27:55'),('card_barcode_show','1','2026-10-19 08:27:55'),('card_border_color','#94a3b8','2026-10-19 08:27:55'),('card_border_w','0.3','2026-10-19 08:27:55'),('card_field_label_w','15','2026-10-19 08:27:55'),('card_fields_size','7.4','2026-10-19 08:27:55'),('card_font_family','cairo','2026-10-19 08:27:55'),('card_font_size','8.2','2026-10-19 08:27:55'),('card_foot_label','رقم التعريف','2026-10-19 08:27:55'),('card_foot_show','1','2026-10-19 08:27:55'),('card_h','54','2026-10-19 08:27:55'),('card_header_bg','#f8fafc','2026-10-19 08:27:55'),('card_inst_size','7.6','2026-10-19 08:27:55'),('card_label','بطاقة المطعم','2026-10-19 08:27:55'),('card_label_color','#0c7273','2026-10-19 08:27:55'),('card_label_font','cairo','2026-10-19 08:27:55'),('card_label_show','1','2026-10-19 08:27:55'),('card_label_size','12.8','2026-10-19 08:27:55'),('card_logo_show','0','2026-10-19 08:27:55'),('card_logo_size','9','2026-10-19 08:27:55'),('card_official_size','6.4','2026-10-19 08:27:55'),('card_photo_h','21','2026-10-19 08:27:55'),('card_photo_show','1','2026-10-19 08:27:55'),('card_photo_w','17','2026-10-19 08:27:55'),('card_primary','#0e7490','2026-10-19 08:27:55'),('card_qr_show','1','2026-10-19 08:27:55'),('card_qr_size','22','2026-10-19 08:27:55'),('card_radius','3','2026-10-19 08:27:55'),('card_show_birth','1','2026-10-19 08:27:55'),('card_show_name','1','2026-10-19 08:27:55'),('card_show_period','1','2026-10-19 08:27:55'),('card_show_reg_no','1','2026-10-19 08:27:55'),('card_show_specialty','1','2026-10-19 08:27:55'),('card_w','96','2026-10-19 08:27:55'),('country','الجمهورية الجزائرية الديمقراطية الشعبية','2026-10-19 08:27:55'),('currency','دج','2026-10-19 08:27:55'),('days_per_month','18','2026-10-19 08:27:55'),('directorate','مديرية التكوين والتعليم المهنيين لولاية توقرت','2026-10-19 08:27:55'),('employee_card_back_info_show','1','2026-10-19 08:27:55'),('employee_card_back_note','','2026-10-19 08:27:55'),('employee_card_back_note_show','0','2026-10-19 08:27:55'),('employee_card_barcode_h','7','2026-10-19 08:27:55'),('employee_card_barcode_show','1','2026-10-19 08:27:55'),('employee_card_border_color','#8395af','2026-10-19 08:27:55'),('employee_card_border_w','0.3','2026-10-19 08:27:55'),('employee_card_field_label_w','15','2026-10-19 08:27:55'),('employee_card_fields_size','7.4','2026-10-19 08:27:55'),('employee_card_font_family','cairo','2026-10-19 08:27:55'),('employee_card_font_size','8.2','2026-10-19 08:27:55'),('employee_card_foot_label','','2026-10-19 08:27:55'),('employee_card_foot_show','0','2026-10-19 08:27:55'),('employee_card_h','54','2026-10-19 08:27:55'),('employee_card_header_bg','#e7f2fd','2026-10-19 08:27:55'),('employee_card_inst_size','7.6','2026-10-19 08:27:55'),('employee_card_label','بطاقة المطعم (موظف)','2026-10-19 08:27:55'),('employee_card_label_color','#0c5f75','2026-10-19 08:27:55'),('employee_card_label_font','cairo','2026-10-19 08:27:55'),('employee_card_label_show','1','2026-10-19 08:27:55'),('employee_card_label_size','12','2026-10-19 08:27:55'),('employee_card_logo_show','0','2026-10-19 08:27:55'),('employee_card_logo_size','9','2026-10-19 08:27:55'),('employee_card_official_size','6.4','2026-10-19 08:27:55'),('employee_card_photo_h','21','2026-10-19 08:27:55'),('employee_card_photo_show','1','2026-10-19 08:27:55'),('employee_card_photo_w','17','2026-10-19 08:27:55'),('employee_card_primary','#0e586c','2026-10-19 08:27:55'),('employee_card_qr_show','1','2026-10-19 08:27:55'),('employee_card_qr_size','22','2026-10-19 08:27:55'),('employee_card_radius','3','2026-10-19 08:27:55'),('employee_card_show_birth','1','2026-10-19 08:27:55'),('employee_card_show_name','1','2026-10-19 08:27:55'),('employee_card_show_period','1','2026-10-19 08:27:55'),('employee_card_show_reg_no','1','2026-10-19 08:27:55'),('employee_card_show_specialty','1','2026-10-19 08:27:55'),('employee_card_w','96','2026-10-19 08:27:55'),('employee_meal_price','25','2026-10-19 08:27:55'),('employee_price_month','','2026-10-19 08:27:55'),('employee_price_quarter','','2026-10-19 08:27:55'),('expiry_alert_days','2','2026-10-19 08:27:55'),('institution_address','','2026-10-19 08:27:55'),('institution_code','','2026-10-19 08:27:55'),('institution_email','','2026-10-19 08:27:55'),('institution_logo','','2026-10-19 08:27:55'),('institution_name','مركز التكوين المهني والتمهين المجاهد بوكرش محمد الزاوية العابدية','2026-10-19 08:27:55'),('institution_phone','','2026-10-19 08:27:55'),('meal_price','15','2026-10-19 08:27:55'),('ministry','وزارة التكوين والتعليم المهنيين','2026-10-19 08:27:55'),('price_month','','2026-10-19 08:27:55'),('price_quarter','','2026-10-19 08:27:55'),('print_cards_per_page','10','2026-10-19 08:27:55'),('print_margin_mm','8','2026-10-19 08:27:55');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `trainees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trainees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(255) DEFAULT NULL,
  `electronic_reg_no` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name_fr` varchar(255) DEFAULT NULL,
  `first_name_fr` varchar(255) DEFAULT NULL,
  `gender` enum('M','F') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `birth_wilaya` varchar(255) DEFAULT NULL,
  `residence_wilaya` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `mother_last_name` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `nin` varchar(255) DEFAULT NULL,
  `specialty` varchar(255) DEFAULT NULL,
  `diploma` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL,
  `training_mode` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `eligible` tinyint(1) NOT NULL DEFAULT 0,
  `file_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `deliberation_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `account_cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `special_needs` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `source_photo` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `registered_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trainees_electronic_reg_no_unique` (`electronic_reg_no`),
  UNIQUE KEY `trainees_reg_no_unique` (`reg_no`),
  KEY `trainees_nin_index` (`nin`),
  KEY `trainees_specialty_index` (`specialty`),
  KEY `trainees_last_name_index` (`last_name`),
  KEY `trainees_phone_index` (`phone`),
  KEY `trainees_start_date_index` (`start_date`),
  KEY `trainees_created_at_index` (`created_at`),
  KEY `trainees_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `trainees` WRITE;
/*!40000 ALTER TABLE `trainees` DISABLE KEYS */;
INSERT INTO `trainees` VALUES (1,'2396','Ju992396','بوشندوقة','دحمان','BOUCHANDOUKA','Dahmane','M','2001-07-05','الزاوية العابدية','توقرت',NULL,'dahmanebouchandouka2001@gmail.com','0652200473',NULL,'بوشندوقة','الزهرة','null','100011098002270006','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_b096de0c-82b4-4b66-90d3-463435495ebc.jpg',NULL,'2026-08-08 12:51:38',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(2,'67099','ANXF67099','مهدي','أحمد','MEHDI','Ahmed','M','2001-03-28','تقرت','توقرت',NULL,'mehdikhireddine35@gmail.com','0556913603',NULL,'بومدين','العالية','null','100011099002980008','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_f4b7a797-0191-4393-beb6-f0451447305d.jpg',NULL,'2026-08-12 12:44:04',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(3,'477133','wo5L477133','بن زازه','عبد الكريم','BENZAZA','Abdelkrim','M','2004-03-21','الطيبات','توقرت',NULL,'karimrimzazakarim@gmail.com','0777214434',NULL,'خذيري','فطيمه بنت علي','حي اللويبد','100041102002030008','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_e6c6ddef-637f-4b96-9d08-4b6e76fa522f.jpg',NULL,'2026-09-23 07:34:56',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(4,'184194','Q6Va184194','سلفاوي','عبد الجبار','SELFAOUI','Abdeldjebar','M','2005-12-10','بني أسود','توقرت',NULL,'amineselfaoui16@gmail.com','0655419496',NULL,'سلفاوي','فاطمة','حي الفتح بتبسبست','100051096007950003','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_00e152d3-7f87-4674-b468-30481be33f12.jpg',NULL,'2026-08-31 09:42:29',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(5,'472920','shD2472920','بالحسين','عثمان','BELHOCINE','Othmane','M','2006-12-09','تقرت','توقرت',NULL,'othmanebel2006@gmail.com','0668627690',NULL,'بن لعمودي','ميمية بنت محمد','عين الصحراء النزلة','100061099012920001','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_e37390d0-658f-44fa-87c0-1ab2ce020561.jpg',NULL,'2026-09-22 20:03:36',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(6,'679413','a7yR679413','غيلاني','محمد شاكر الله','GHILANI','Mohammed Chaker Allak','M','2006-12-28','تقرت','توقرت',NULL,'chakerghilani18@gmail.com','0664874456',NULL,'بدوي','هجيرة بنت أحمد','الزاوية العابدية','100061099013650003','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,NULL,NULL,'2026-09-29 18:23:20',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(7,'446305','Rues446305','بوكلبة','عبد  الواجد','BOUKELBA','Abdelouadjad','M','2007-07-24','تبسبست','توقرت',NULL,'thoore43@gmail.com','0541971907',NULL,'زنو','ناجية','تبسبست','100071096004640009','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,NULL,NULL,'2026-09-21 17:44:53',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(8,'543466','S9PZ543466','بن شنة','خالد','BENCHENNA','Khaled','M','2007-07-25','النزلة','توقرت',NULL,'kkhaledtop795@gmail.com','0797397228',NULL,'بن سعدي','مباركة','كتلة 321 رقم 09 عين الصحراء النزلة','100071097007820002','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_29f50ad7-a932-4ee4-b669-ea758ce63508.jpg',NULL,'2026-09-24 16:59:35',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(9,'726281','QSSJ726281','عبعوب','نورالدين','ABOUB','Nour Eddine','M','2007-06-16','الزاوية العابدية','توقرت',NULL,'hhghhccc129@gmail.com','0562152327',NULL,'بكاري','حياة بنت الصغير','الزاوية العابدية','100071098002680003','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_22681f90-9346-421c-9175-dad58bb90511.jpg',NULL,'2026-09-30 18:21:19',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(10,'691398','1Hsn691398','وقاد','محمد ضياء الدين','OUGGAD','Mohammed Dhia Eddine','M','2007-05-13','تقرت','توقرت','توقرت','mohammedouggad2007@gmail.com','0542945786',NULL,'لموشي','فتيحة','حي بومرداس الطيبات','100071099005650001','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,NULL,NULL,'2026-09-30 08:04:33',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(11,'745448','7JVz745448','غويني','زياد','GHOUINI','Ziad','M','2008-09-20','بني أسود','توقرت',NULL,'zizoghouini1@gmail.com','0561532478',NULL,'عيشور','كريمة ياسمينه','حي البدوعات تبسبست','100081096006810004','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_44c9c86b-ce30-4cda-8288-70b70426795e.jpg',NULL,'2026-10-01 15:35:18',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(12,'257487','S2pL257487','طرية','اسحاق','TRYA','Ishak','M','2008-11-26','تبسبست','توقرت',NULL,'tryashaq22@gmail.com','0697174853',NULL,'بختة','آسية','حي الفتح تبسبست','100081096008850008','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_78807c37-d86d-45fe-ac0d-11f616dab20e.jpg',NULL,'2026-09-10 06:49:01',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(13,'213307','bCNO213307','التهامي','منتصر بالله','TIHAMI','Montser Billah','M','2008-01-03','النزلة','توقرت',NULL,'montaser8@yopmail.com','0655917606',NULL,'حدو','راضية','النزلة','100081097000080008','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_a0003a07-e47e-4ef8-a37f-485775f9c499.jpg',NULL,'2026-09-05 12:07:08',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(14,'164986','2Qdh164986','جمل','صابر','DJEMEL','Saber','M','2008-09-20','النزلة','توقرت',NULL,'sabrsbry396@gmail.com','0655660743',NULL,'غندير','يمينة','عين الصحراء النزلة','100081097010340004','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_080bf4ab-c6da-4f85-bacc-56454aa2a4da.jpg',NULL,'2026-08-26 19:07:57',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(15,'213484','95kl213484','خرفي','أبوبكر','KHERFI','Aboubakeur','M','2008-12-13','النزلة','توقرت',NULL,'boubakr@yopmail.com','0665475344',NULL,'خرفي','حياة','النزلة','100081097013730003','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_b2820b79-fe4e-48ac-8c9b-15895347c842.jpg',NULL,'2026-09-05 12:50:58',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(16,'585261','O8t2585261','بن هنية','سيف الدين','BENHANIA','Seif Eddine','M','2008-04-29','الزاوية العابدية','توقرت',NULL,'ttetyy@gmail.com','0675948203',NULL,'بريك','نعيمة بنت عبد العزيز','حي 382 المستقبل تقرت','100081098001400005','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_61c5b821-840b-4b9d-b7af-b7d692b01ad1.jpg',NULL,'2026-09-26 19:35:45',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(17,'657347','aFuh657347','غيابه','أسامة','GHIABA','Oussama','M','2008-10-28','تقرت','توقرت',NULL,'oussama55@gmail.com','0558721248',NULL,'تراكه','عائشة بنت أحمد','el mostakbel touggourt','100081099012880003','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,NULL,NULL,'2026-09-29 10:40:10',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(18,'654626','ySaP654626','مناصرية','صلاح الدين',NULL,NULL,NULL,'2009-11-07',NULL,'توقرت','توقرت','mohmdmanassria@gmail.com','0664263530',NULL,NULL,NULL,'كتلة 59رقم3 صحراوي العيد','100091097013310002','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,NULL,NULL,'2026-09-29 10:03:05',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(19,'232854','B5vw232854','بن رمضانه','معتصم بالله','BENRAMDANA','Mouatassim BILLAH','M','2009-06-25','الزاوية العابدية','توقرت',NULL,'swmys3667@gmail.com','0558976298',NULL,'بن عقه','زهيه بنت محمد','كتلة 103 رقم 04 حي المنصور الزاوية العابدية','100091098002660001','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,NULL,'يرجى تحميل الملف','2026-09-07 21:18:07',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(20,'544196','xYYt544196','غولة','طلحة',NULL,NULL,NULL,'2009-02-20',NULL,'المنيعة',NULL,'ghoula2009@gmail.com','0696464032',NULL,NULL,NULL,'حي تبسبست تقرت','100091492001620001','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,NULL,NULL,'2026-09-24 17:20:10',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(21,'317824','2KpS317824','حاج سعيد','عبد الكريم',NULL,NULL,NULL,'1998-12-25',NULL,'توقرت',NULL,'mrsatano4@gmail.com','0540575696',NULL,NULL,NULL,'حي محمد خميستي 22 مسكن رقم 05','109981099009170006','كهرباء السيارات','ش.ك.م | CAP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_225df9d3-1108-4e0c-b15a-ade0af3de938.jpg',NULL,'2026-09-14 21:48:05',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(22,'593914','Ch05593914','بوشندوقة','بن عيسى',NULL,NULL,NULL,'2005-02-16',NULL,'توقرت',NULL,'hamad2005dz@gmail.com','0562364376',NULL,NULL,NULL,'حي 05 جويلية الزاوية العابدية توقرت','100051098000490003','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_569811f9-da02-4f07-8fe1-59725909139d.jpg',NULL,'2026-09-27 09:57:30',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(23,'511099','YbuG511099','دوغة','عبد الحليم',NULL,NULL,NULL,'2008-09-07',NULL,'توقرت',NULL,'jhkjkbjhkbb2002@gmail.com','0665382202',NULL,NULL,NULL,'المقارين ولاية تقرت','100081108002660000','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_e0af7988-4e55-4ed3-b05d-f1e0a8b0a516.jpg',NULL,'2026-09-23 20:44:07',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(24,'420293','Uy3N420293','كريكر','عبد النافع',NULL,NULL,NULL,'2009-01-07',NULL,'توقرت',NULL,'nafkrykr@gmail.com','0652308462',NULL,NULL,NULL,'الزاوية العابدية','100091098000100000','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_cc7e5158-4905-4e07-bd86-62ecf8655214.jpg',NULL,'2026-09-20 19:55:21',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(25,'134732','0iLW134732','حملاوي','عبد النور','HAMLAOUI','Abdenour','M','2009-10-07','تقرت','توقرت',NULL,'hemlaouiabdelnour@gmail.com','0662925091',NULL,'العابد','خديجة','كتلة454 رقم 07 حي الخلوة الزاوية العابدية','100091099013220009','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_3ed4ed93-af12-4f10-8805-f52dbeca5032.png',NULL,'2026-08-21 10:09:02',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(26,'400237','fgtk400237','بن قويدر','محجوبة',NULL,NULL,NULL,'2003-11-20',NULL,'الجلفة',NULL,'benshry2@gmail.com','0673098615',NULL,NULL,NULL,'حي القبلة المقارين','110030625024280005','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_b822928f-de55-4568-9fc5-ae0bd7f97cca.jpg',NULL,'2026-09-20 09:15:23',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(27,'657280','j2S7657280','بالهاني','غزلان','BELHANI','Ghezlan',NULL,'2006-06-10','النزلة','توقرت',NULL,'ghzlanblhany@gmail.com','0663576756',NULL,'قمو','زهية','الزاوية العابدية','110061097005570006','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,NULL,NULL,'2026-09-29 10:38:53',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(28,'109417','2poA109417','غياط','سمر','GHIAT','Samer',NULL,'2007-05-10','تبسبست','توقرت',NULL,'samarghait@gmail.com','0779784122',NULL,'بالغمري','مسعودة','الزاوية العابدية','110071096002950003','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_ea24826e-d8c9-4385-b84b-99dce8ef4fc4.jpg',NULL,'2026-08-17 17:57:35',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(29,'142476','AaWA142476','خياط','هديل','KHIAT','Hadil',NULL,'2007-10-26','الزاوية العابدية','توقرت',NULL,'amoulaamaldz2002@gmail.com','0663007226',NULL,'خميسى','خديجة بنت مصطفى','الزاوية العابدية','110071098004340001','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_d8a9d58d-76ae-4af6-a062-87044c2a7d14.jpeg',NULL,'2026-09-16 11:02:00',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(30,'565798','2DsF565798','صايم','شيماء',NULL,NULL,NULL,'2008-02-21',NULL,'توقرت',NULL,'saim2008@gmail.com','0557619032',NULL,NULL,NULL,'تبسبست','110081096001390001','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_deabec84-c640-4eb5-838e-3dc11d2b55f9.jpg',NULL,'2026-09-26 09:20:47',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(31,'365063','2bjR365063','ريغي','ماريا',NULL,NULL,NULL,'2008-05-08',NULL,'توقرت',NULL,'zydmarya5@gmail.com','0666669078',NULL,NULL,NULL,'كتلة 111 رقم 20 حي المجاهد','110081096003280002','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_5d504fb1-7c22-4985-973a-2c46fd5feaf2.jpg',NULL,'2026-09-17 10:24:06',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(32,'312641','d7LY312641','باباعربي','إزدهار','BABA ARBI','Izdihar',NULL,'2008-02-11','الزاوية العابدية','توقرت',NULL,'krimo202066@gmail.com','0672022682',NULL,'بركبيه','سهيله بنت محمد','الزاوية العابدية','110081098000660004','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_739152ee-fca5-490d-9f3a-d26a603a4b26.jpg',NULL,'2026-09-14 16:41:13',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(33,'321524','mVNn321524','العابد','سجود',NULL,NULL,NULL,'2008-11-05',NULL,'توقرت',NULL,'labedsoudjoud08@gmail.com','0672663049',NULL,NULL,NULL,'كتلة 71 رقم 11 حي وادي ريغ','110081098004230002','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_7ab1e23e-8620-40d6-b8aa-85d625c87380.jpg',NULL,'2026-09-15 09:47:45',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(34,'341001','RyLA341001','غويني','سعاد',NULL,NULL,NULL,'2008-12-27',NULL,'توقرت',NULL,'zhrtgwyny22@gmail.com','0655747938',NULL,NULL,NULL,'الزاوية العابدية','110081098005180008','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_e4243add-51ec-4081-b800-7455ace95b04.jpg',NULL,'2026-09-16 09:13:23',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(35,'566433','Rp5F566433','عرعار','امينة',NULL,NULL,NULL,'2009-02-27',NULL,'توقرت',NULL,'arar2009@gmail.com','0654193024',NULL,NULL,NULL,'كتلة 147 رقم 04 حي المجاهد تبسبست','110091096001510002','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_5d5191b3-1b9b-492f-80fb-13d9f111fc73.jpg',NULL,'2026-09-26 09:40:46',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(36,'60393','bO6l60393','بوليفة','فاطمه الزهراء','BOULIFA','Fatma Zahra',NULL,'2009-01-03','مقر','توقرت',NULL,'fatimazohraboulifa@gmail.com','0554795911',NULL,'بن دحمان','أم الخير','مقر','110091107000010009','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_5171f89d-125e-459b-a717-ac1e266c84ba.jpg',NULL,'2026-08-11 19:56:54',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(37,'723144','CPVV723144','دوغة','نور الهدى',NULL,NULL,NULL,'2009-05-18',NULL,'توقرت',NULL,'nourelhouda2009@gmail.com','0657193092',NULL,NULL,NULL,'تقرت','110091108001600004','عـامــــــل في الميكـــرو معلـوماتيـــة','ش.ت.م | CMP','الرابعة متوسط | 4EM ANNEE MOYENNE','حضوري أولي  | Présentiel','2026-10-04','2028-04-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_c2fb0e2f-0e55-41c1-a4ad-7448bc03dfbf.jpg',NULL,'2026-09-30 17:11:16',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(38,'699416','zpij699416','بوشندوقة','منال',NULL,NULL,NULL,'2008-12-16',NULL,'توقرت',NULL,'vhhcyg491@gmail.com','0663397328',NULL,NULL,NULL,'حي 05 جويلية الزاوية العابدية تقرت','110081098004900000','الطرز','ش.ك.م | CAP','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,NULL,NULL,'2026-09-30 10:34:39',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(39,'756385','bfqb756385','قمو','الزهرة',NULL,NULL,NULL,'2009-02-03',NULL,'توقرت','توقرت','guemmou2009@gmail.com','0697960711',NULL,NULL,NULL,'كتلة 38 رقم 14','110091098000600008','الطرز','ش.ك.م | CAP','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,NULL,NULL,'2026-10-04 09:26:34',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(40,'245155','t857245155','بن نونه','ساره','BENNOUNA','Sara',NULL,'2009-04-02','الزاوية العابدية','توقرت',NULL,'mom.nokc546467@gmail.vom','0655058948',NULL,'قمو','جميله بنت السعيد','الزاوية العابدية','110091098001450007','الطرز','ش.ك.م | CAP','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,NULL,NULL,'2026-09-09 08:28:48',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(41,'386519','kOJS386519','عيساني','انوار',NULL,NULL,NULL,'2009-07-05',NULL,'توقرت',NULL,'ysanyanwr0@gmail.com','0658843995',NULL,NULL,NULL,'حي السلام الزاوية العابدية','110091098001930008','الطرز','ش.ك.م | CAP','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_f5e79ee6-ec44-4d8c-92ce-5f4aadc01e1c.jpg',NULL,'2026-09-19 09:01:13',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(42,'652070','HaKK652070','مزرقط','ماريا',NULL,NULL,NULL,'2009-02-12',NULL,'توقرت','توقرت','maria@2009gmail.com','0672627122',NULL,NULL,NULL,'حي 5جويلية','110091099002200003','الطرز','ش.ك.م | CAP','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,NULL,NULL,'2026-09-29 09:22:07',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(43,'474400','E3ED474400','الأعور','علي',NULL,NULL,NULL,'2007-10-01',NULL,'توقرت',NULL,'alilaouer@gmail.com','0666258402',NULL,NULL,NULL,'تقرت','100071099011660002','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_ef61d2cb-a500-43a3-b207-9b6f92231922.jpg',NULL,'2026-09-22 21:09:18',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(44,'741693','escJ741693','ابي ميلود','محمد نضال',NULL,NULL,NULL,'2008-10-04',NULL,'ورقلة',NULL,'abimouloudnidal@gmail.com','0777252135',NULL,NULL,NULL,'النزلة','100081089035860009','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_9e200987-f9e5-4474-85d0-2b6aca25bec9.jpg',NULL,'2026-10-01 10:58:48',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(45,'586688','qoia586688','بوعلي','سيف الدين','BOUALI','Seif Eddine','M','2008-01-30','الزاوية العابدية','توقرت',NULL,'seif2008@gmail.com','0654017651',NULL,'حنونه','حياة بنت مسعود','الزاوية العابدية','100081098000600001','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_80df0c3b-4f06-48ff-a1a9-00cb9e76567e.jpg',NULL,'2026-09-26 20:49:35',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(46,'319920','gCiS319920','بن قويدر','خالد',NULL,NULL,NULL,'2008-09-08',NULL,'توقرت',NULL,'benkouider1122@gmail.com','0562572239',NULL,NULL,NULL,'كتلة 175 رقم 01 حي الباستين','100081098003280009','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_68aa37a7-8e4b-4dc9-9bc2-678d64792e75.jpg',NULL,'2026-09-15 08:44:14',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(47,'63290','czF063290','دومان','عماد','DOUMANE','Imad','M','2009-12-05','تنس','شلف','توقرت','amr943811@gmail.com','0549263545',NULL,'بن شاعة','جمعية','حي المجاهد تبسبست تقرت','100090032022670006','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_39feb81d-4561-4276-a13b-48cc4f75b0fb.jpg',NULL,'2026-08-12 09:40:54',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(48,'305028','0E6d305028','عزيزي','لقمان','AZIZI','Lokmane','M','2009-05-29','تبسبست','توقرت',NULL,'azizilokman462@gmail.com','0654446403',NULL,'بن زينة','فاطمة الزهرة','كتلة 45 رقم 15 تبسبست','100091096003940000','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_b35bb0bb-1cd4-4cbe-ade6-05e9120157d8.jpg',NULL,'2026-09-14 11:10:28',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(49,'690643','awgO690643','مجوجة','عبد الرحمان',NULL,NULL,NULL,'2009-11-09',NULL,'توقرت','توقرت','medjoudja2009@gmail.com','0553875115',NULL,NULL,NULL,'كتلة 69 تبسبست','100091096008700003','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,NULL,NULL,'2026-09-30 07:37:55',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(50,'623739','Wo1I623739','بلوزي','يوسف',NULL,NULL,NULL,'2009-03-27',NULL,'توقرت',NULL,'blouziw@gmail.com','0673167918',NULL,NULL,NULL,'حي الورود الزاوية العابدية','100091097003670001','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_855b4cae-adb4-4b6c-a7fc-4a099d76e0ce.jpg',NULL,'2026-09-28 09:56:15',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(51,'622322','3UPF622322','اماني','محمد اكرم',NULL,NULL,NULL,'2009-01-14',NULL,'توقرت',NULL,'amanikarim977@gmail.com','0665311812',NULL,NULL,NULL,'حي 5 جويبية الزاوية العابدية','100091098000270001','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_e94c6720-5636-45cd-9297-dfde2268256a.jpg',NULL,'2026-09-28 09:30:13',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(52,'367222','E1Br367222','مقنش','عبد الرزاق',NULL,NULL,NULL,'2009-02-21',NULL,'توقرت',NULL,'bzbnb16@gmail.com','0663780634',NULL,NULL,NULL,'حي 05 جويلية الزاوية العابدية تقرت','100091098000850000','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_9613ad80-fc85-4a76-b8fe-99e58d83b414.jpg',NULL,'2026-09-17 11:17:28',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(53,'560296','y1RM560296','نوي','سليم',NULL,NULL,NULL,'2009-05-23',NULL,'توقرت',NULL,'salimnaoui55@gmail.com','0561756081',NULL,NULL,NULL,'حي 05 جويلية الزاوية العابدية تقرت','100091098002080002','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_ac5ae306-f15d-4d88-9dfa-bcfc3b73283e.jpg',NULL,'2026-09-25 19:57:02',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(54,'159294','bGFJ159294','حنونه','عبد الحق','HANNOUNA','Abdelhak','M','2009-06-19','الزاوية العابدية','توقرت',NULL,'abdalhakhannouna@gmail.com','0655720972',NULL,'صولي','حليمه بنت محمد','حي السلام الزاوية العابدية','100091098002500009','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_9adf876d-56ad-4618-a2d7-dbe08ebc16e2.jpg',NULL,'2026-08-25 21:02:58',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(55,'337097','HtSR337097','بالهاني','سيف الدين',NULL,NULL,NULL,'2010-03-20',NULL,'توقرت',NULL,'sayfesayfe925@gmail.com','0666803760',NULL,NULL,NULL,'الزاوية العابدية','100101097003440007','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_af6ba1df-9bdf-45aa-bd4a-f3aa299c0fda.jpeg',NULL,'2026-09-15 20:39:54',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(56,'404001','I9Ju404001','نوي','محمد',NULL,NULL,NULL,'2010-04-19',NULL,'توقرت',NULL,'nm0562494227@gmail.com','0562494227',NULL,NULL,NULL,'حي 05 جويلية','100101098001770007','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_ab736c2d-50b1-4fda-90d7-2f7b279fe9e6.jpg',NULL,'2026-09-20 10:44:05',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09'),(57,'477281','Wvxk477281','القني','محمد حبيب الرحمان',NULL,NULL,NULL,'2010-08-14',NULL,'توقرت',NULL,'fouzigouni@gmail.com','0657502830',NULL,NULL,NULL,'توقرت','100101105002940008','التركيب الصحي','ش.ت.م.م | CFPS','الطور الابتدائي | CYCLE PRIMAIRE','حضوري أولي  | Présentiel','2026-10-04','2027-10-03',1,1,0,0,'0',NULL,'takwindz_files/photo_path/PhotoID_36_c02eb568-41cc-4938-9001-36667d74d5fe.jpg',NULL,'2026-09-23 07:41:55',NULL,'2026-10-18 10:51:09','2026-10-18 10:51:09');
/*!40000 ALTER TABLE `trainees` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','supervisor','staff','canteen','assistant') NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_role_index` (`role`),
  KEY `users_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'مدير النظام','admin',NULL,'$2y$12$YlbxmMjb5RGqjKK1sXT4o.PvMISeniXjz2SaSLmw.58tJtjs83zHi','admin',1,'2026-10-19 08:52:00',NULL,'2026-10-18 08:27:39','2026-10-19 08:52:00'),(2,'الشاهد محمد لطفي','LOTFI',NULL,'$2y$12$06RgEWqn6ZoD8XhxfSqPf.d24aV5lPVGbuYzdEmlBIYtUyUHtHNhW','assistant',1,'2026-10-19 07:30:29',NULL,'2026-10-19 06:44:51','2026-10-19 07:30:29');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

