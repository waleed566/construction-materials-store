# Construction Materials Store

تطبيق ويب وموبايل لبيع المواد الإنشائية

## المميزات الرئيسية

- ✅ نظام تسجيل وتسجيل دخول المستخدمين
- ✅ عرض المنتجات وتصنيفاتها
- ✅ سلة التسوق
- ✅ نظام الطلبات
- ✅ نظام الدفع
- ✅ لوحة تحكم الإدارة
- ✅ نظام التقييمات
- ✅ البحث والفلترة

## متطلبات التثبيت

- PHP 8.0 أو أحدث
- MySQL 5.7 أو أحدث
- Composer

## التثبيت والإعداد

### 1. استنساخ المستودع

```bash
git clone https://github.com/waleed566/construction-materials-store.git
cd construction-materials-store
```

### 2. تثبيت المتطلبات

```bash
composer install
```

### 3. إعداد قاعدة البيانات

```bash
# انسخ ملف الإعدادات
cp config/database.example.php config/database.php

# عدّل بيانات الاتصال في database.php
nano config/database.php
```

### 4. إنشاء الجداول

```bash
mysql -u root -p construction_materials_store < database/schema.sql
```

### 5. تشغيل الخادم

```bash
php -S localhost:8000 -t public
```

## هيكل المشروع

```
construction-materials-store/
├── app/
│   ├── Controllers/       # معالجات الطلبات
│   ├── Models/            # نماذج قاعدة البيانات
│   └── Database/          # فئات قاعدة البيانات
├── config/
│   └── database.php       # إعدادات قاعدة البيانات
├── database/
│   └── schema.sql         # مخطط قاعدة البيانات
├── public/
│   ├── index.php          # نقطة الدخول الرئيسية
│   ├── css/               # ملفات CSS
│   ├── js/                # ملفات JavaScript
│   └── images/            # الصور
└── composer.json          # متطلبات Composer
```

## نقاط نهاية الـ API

### المصادقة
- `POST /api/auth/register` - تسجيل مستخدم جديد
- `POST /api/auth/login` - تسجيل دخول
- `POST /api/auth/logout` - تسجيل خروج
- `GET /api/auth/profile` - الحصول على بيانات المستخدم

## المساهمة

نرحب بمساهماتك! يرجى إنشاء pull request مع وصف التغييرات.

## الترخيص

هذا المشروع مرخص تحت رخصة MIT.
