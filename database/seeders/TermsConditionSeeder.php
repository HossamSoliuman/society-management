<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TermsCondition;

class TermsConditionSeeder extends Seeder
{
    public function run(): void
    {
        $terms = [
            ['title' => 'Member Terms & Conditions', 'content' => 'Terms for all society members...', 'document_type' => 'member_app', 'version' => '2.1'],
            ['title' => 'Privacy Policy', 'content' => 'Privacy policy for member data protection...', 'document_type' => 'member_app', 'version' => '1.3'],
            ['title' => 'Visitor Terms & Conditions', 'content' => 'Terms for visitors using gate pass...', 'document_type' => 'web_portal', 'version' => '1.0'],
            ['title' => 'Complaint Policy', 'content' => 'Policy for complaint registration and resolution...', 'document_type' => 'web_portal', 'version' => '1.2'],
            ['title' => 'Payment Terms & Conditions', 'content' => 'Terms related to payments and refunds...', 'document_type' => 'member_app', 'version' => '2.0'],
            ['title' => 'Service Usage Policy', 'content' => 'Acceptable use policy for system services...', 'document_type' => 'other_documents', 'version' => '1.0'],
            ['title' => 'Maintenance Policy', 'content' => 'Policy for maintenance and downtime...', 'document_type' => 'web_portal', 'version' => '1.1', 'status' => 'inactive'],
            ['title' => 'Data Retention Policy', 'content' => 'Policy for data retention and deletion...', 'document_type' => 'other_documents', 'version' => '1.0'],
        ];

        foreach ($terms as $term) {
            TermsCondition::create(array_merge($term, ['created_by' => 1]));
        }
    }
}
