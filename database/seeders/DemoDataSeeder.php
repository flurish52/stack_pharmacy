<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // --- Categories ---
        DB::insert("
            INSERT INTO categories (id, name, slug, created_at, updated_at) VALUES
            (1, 'Pain Relief', 'pain-relief', NOW(), NOW()),
            (2, 'Vitamins & Supplements', 'vitamins-supplements', NOW(), NOW()),
            (3, 'Skin Care', 'skin-care', NOW(), NOW()),
            (4, 'Baby Care', 'baby-care', NOW(), NOW()),
            (5, 'First Aid', 'first-aid', NOW(), NOW())
        ");

        // --- Products ---
        DB::insert("
            INSERT INTO products (id, name, slug, description, status, created_at, updated_at) VALUES
            (1, 'Paracetamol 500mg', 'paracetamol-500mg', 'Fast-acting relief for mild to moderate pain and fever.', 'active', NOW(), NOW()),
            (2, 'Vitamin C 1000mg Effervescent', 'vitamin-c-1000mg-effervescent', 'Immune support, orange flavor, dissolves in water.', 'active', NOW(), NOW()),
            (3, 'Ibuprofen 400mg', 'ibuprofen-400mg', 'Anti-inflammatory pain relief tablets.', 'active', NOW(), NOW()),
            (4, 'Aloe Vera Soothing Gel', 'aloe-vera-soothing-gel', 'Gentle gel for irritated or sun-exposed skin.', 'active', NOW(), NOW()),
            (5, 'Baby Diaper Rash Cream', 'baby-diaper-rash-cream', 'Protective barrier cream for sensitive baby skin.', 'active', NOW(), NOW()),
            (6, 'Adhesive Bandage Strips (Pack of 40)', 'adhesive-bandage-strips-40', 'Assorted sizes, breathable, for minor cuts and scrapes.', 'active', NOW(), NOW()),
            (7, 'Multivitamin Daily Tablets', 'multivitamin-daily-tablets', 'Once-daily multivitamin for general wellness.', 'active', NOW(), NOW()),
            (8, 'Antiseptic Liquid 250ml', 'antiseptic-liquid-250ml', 'For cleaning minor wounds and skin surfaces.', 'active', NOW(), NOW())
        ");

        // --- Category ↔ Product pivot (is_primary marks the canonical category) ---
        DB::insert("
            INSERT INTO category_product (category_id, product_id, is_primary, created_at, updated_at) VALUES
            (1, 1, 1, NOW(), NOW()),
            (5, 1, 0, NOW(), NOW()),
            (2, 2, 1, NOW(), NOW()),
            (1, 3, 1, NOW(), NOW()),
            (3, 4, 1, NOW(), NOW()),
            (4, 5, 1, NOW(), NOW()),
            (5, 6, 1, NOW(), NOW()),
            (2, 7, 1, NOW(), NOW()),
            (5, 8, 1, NOW(), NOW()),
            (3, 8, 0, NOW(), NOW())
        ");

        // --- Product variants — every product needs at least one, per schema rule ---
        DB::insert("
            INSERT INTO product_variants (product_id, variant_name, sku, price, stock_quantity, created_at, updated_at) VALUES
            (1, '500mg - 20 tablets', 'PARA-500-20', 850.00, 120, NOW(), NOW()),
            (1, '500mg - 100 tablets', 'PARA-500-100', 3200.00, 40, NOW(), NOW()),
            (2, '20 tablets', 'VITC-1000-20', 2500.00, 60, NOW(), NOW()),
            (3, '400mg - 20 tablets', 'IBU-400-20', 1100.00, 8, NOW(), NOW()),
            (4, '150ml tube', 'ALOE-150', 1800.00, 0, NOW(), NOW()),
            (5, '100g tube', 'DIAPER-100', 2200.00, 35, NOW(), NOW()),
            (6, 'Pack of 40', 'BAND-40', 1500.00, 90, NOW(), NOW()),
            (7, '30 tablets', 'MULTI-30', 4500.00, 50, NOW(), NOW()),
            (7, '60 tablets', 'MULTI-60', 8000.00, 25, NOW(), NOW()),
            (8, '250ml bottle', 'ANTI-250', 1950.00, 45, NOW(), NOW())
        ");

        // --- Services (for the landing page + Services page) ---
        DB::insert("
            INSERT INTO services (id, name, description, whatsapp_message, is_active, created_at, updated_at) VALUES
            (1, 'General Consultation', 'Speak with our pharmacist about any health concern.', 'Hi, I would like to book a general consultation.', 1, NOW(), NOW()),
            (2, 'Blood Pressure Check', 'Quick, free blood pressure screening in-store.', 'Hi, I would like to know more about your blood pressure check service.', 1, NOW(), NOW()),
            (3, 'Medication Review', 'A review of everything you currently take, to check for interactions.', 'Hi, I would like a medication review.', 1, NOW(), NOW())
        ");

        // --- Training (singleton row, id must be 1 to match TrainingController's updateOrCreate) ---
        DB::insert("
            INSERT INTO trainings (id, title, description, created_at, updated_at) VALUES
            (1, 'Community Health Training', 'Short courses on basic first aid, medication safety, and family health — taught by our pharmacist.', NOW(), NOW())
        ");

        // --- Contact channels (footer) ---
        DB::insert("
            INSERT INTO contact_channels (platform, handle, url, display_order, created_at, updated_at) VALUES
            ('whatsapp', '2348012345678', NULL, 1, NOW(), NOW()),
            ('phone', '08012345678', NULL, 2, NOW(), NOW()),
            ('instagram', '@stackpharmacy', 'https://instagram.com/stackpharmacy', 3, NOW(), NOW()),
            ('email', 'hello@stackpharmacy.com', NULL, 4, NOW(), NOW())
        ");

        // --- Pickup points (checkout dropdown) ---
        DB::insert("
            INSERT INTO pickup_points (name, address, is_active, created_at, updated_at) VALUES
            ('Stack Pharmacy — Main Branch', '12 Herbert Macaulay Way, Yaba, Lagos', 1, NOW(), NOW()),
            ('Stack Pharmacy — Ikeja Branch', '45 Allen Avenue, Ikeja, Lagos', 1, NOW(), NOW())
        ");
    }
}
