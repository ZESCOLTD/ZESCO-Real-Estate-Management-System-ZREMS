<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Modules
        |--------------------------------------------------------------------------
        */

        $modules = [

            // Core
            'dashboard',
            'department_operations',

            // Acquisition
            'acquisition_requests',
            'due_diligence',
            'spms_lookup',

            // GIS
            'gis_mapping',
            'gis_land_parcels',

            // Conversion / Convergence
            'conversion',
            'convergence',

            // Management
            'asset_management',

            // Assets
            'land_properties',
            'lease_agreements',
            'asset_register',
            'wayleaves',
            'real_estate_projects',

            // Approvals
            'acquisition_approvals',
            'due_diligence_approvals',
            'asset_management_approvals',

            // Services
            'services',

            // Reports
            'reports',

            // Settings
            'users',
            'workflow',

            // Configurations
            'configuration',
            'configuration_sheq_document_ribbons',
            'configuration_payment_modes',
            'configuration_payment_types',
            'configuration_document_types',
            'configuration_units',
            'configuration_ownership_status',
            'configuration_provinces',
            'configuration_services',
            'configuration_team_roles',
            'configuration_workflow_definitions',
        ];

        /*
        |--------------------------------------------------------------------------
        | Standard Actions
        |--------------------------------------------------------------------------
        */

        $actions = [
            'view',
            'create',
            'update',
            'delete',
            'export',
            'submit',
            'approve',
            'manage',
        ];

        /*
        |--------------------------------------------------------------------------
        | Generate Standard Permissions
        |--------------------------------------------------------------------------
        */

        foreach ($modules as $module) {

            foreach ($actions as $action) {

                Permission::firstOrCreate([
                    'name' => "{$module}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Contracts Module
        |--------------------------------------------------------------------------
        |
        | Contracts has a more detailed workflow than the standard
        | CRUD modules, therefore its permissions are explicitly defined.
        |
        */

        $contractsPermissions = [

            /*
            |--------------------------------------------------------------------------
            | Contract Requests
            |--------------------------------------------------------------------------
            */

            'contracts.requests.view',
            'contracts.requests.create',
            'contracts.requests.update',
            'contracts.requests.delete',
            'contracts.requests.submit',

            // Workflow
            'contracts.requests.review',
            'contracts.requests.return',
            'contracts.requests.reject',
            'contracts.requests.classify',
            'contracts.requests.approve',

            /*
            |--------------------------------------------------------------------------
            | Contract Request Attachments
            |--------------------------------------------------------------------------
            */

            'contracts.attachments.view',
            'contracts.attachments.upload',
            'contracts.attachments.download',
            'contracts.attachments.delete',

            /*
            |--------------------------------------------------------------------------
            | Contract Classification
            |--------------------------------------------------------------------------
            */

            'contracts.classification.view',
            'contracts.classification.create',
            'contracts.classification.update',
            'contracts.classification.delete',
            'contracts.classification.manage',

            /*
            |--------------------------------------------------------------------------
            | Contracts
            |--------------------------------------------------------------------------
            */

            'contracts.contracts.view',
            'contracts.contracts.create',
            'contracts.contracts.update',
            'contracts.contracts.delete',
            'contracts.contracts.approve',
            'contracts.contracts.manage',

            /*
            |--------------------------------------------------------------------------
            | Contract Documents
            |--------------------------------------------------------------------------
            */

            'contracts.documents.view',
            'contracts.documents.upload',
            'contracts.documents.download',
            'contracts.documents.delete',

            /*
            |--------------------------------------------------------------------------
            | Contract Lifecycle
            |--------------------------------------------------------------------------
            */

            'contracts.lifecycle.view',
            'contracts.lifecycle.manage',

            /*
            |--------------------------------------------------------------------------
            | Contract Obligations
            |--------------------------------------------------------------------------
            */

            'contracts.obligations.view',
            'contracts.obligations.create',
            'contracts.obligations.update',
            'contracts.obligations.delete',
            'contracts.obligations.manage',

            /*
            |--------------------------------------------------------------------------
            | Contract Variations
            |--------------------------------------------------------------------------
            */

            'contracts.variations.view',
            'contracts.variations.create',
            'contracts.variations.update',
            'contracts.variations.delete',
            'contracts.variations.approve',

            /*
            |--------------------------------------------------------------------------
            | Contract Reporting
            |--------------------------------------------------------------------------
            */

            'contracts.reports.view',
            'contracts.reports.export',

        ];

        foreach ($contractsPermissions as $permission) {

            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $roles = [

            'Super Administrator',

            'Administrator',

            'Acquisition Officer',

            'Due Diligence Officer',

            'GIS Officer',

            'Asset Officer',

            'Approving Officer',

            'Senior Real Estate Officer',
            'Principal Real Estate Officer',
            'Manager Real Estate',
            'Senior Manager Real Estate',
            'Head Real Estate',
            'Director Real Estate',

            'Services Officer',

            'Workflow Administrator',

            'Configuration Administrator',

            // Contracts
            'Contract Requester',
            'Contract Supervisor',
            'Legal Officer',
            'Procurement Officer',
            'Contract Administrator',

        ];

        foreach ($roles as $role) {

            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        foreach ([
            'Senior Real Estate Officer',
            'Principal Real Estate Officer',
            'Manager Real Estate',
            'Senior Manager Real Estate',
            'Head Real Estate',
            'Director Real Estate',
        ] as $roleName) {
            Role::findByName($roleName)->syncPermissions([
                'dashboard.view',
                'real_estate_projects.view',
                'real_estate_projects.approve',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Super Administrator
        |--------------------------------------------------------------------------
        */

        Role::findByName('Super Administrator')
            ->syncPermissions(Permission::all());


        /*
        |--------------------------------------------------------------------------
        | Administrator
        |--------------------------------------------------------------------------
        */

        Role::findByName('Administrator')
            ->syncPermissions([

                'dashboard.view',
                'department_operations.view',

                /*
                |--------------------------------------------------------------------------
                | Acquisition
                |--------------------------------------------------------------------------
                */

                'acquisition_requests.view',
                'acquisition_requests.create',
                'acquisition_requests.update',
                'acquisition_requests.submit',

                'due_diligence.view',
                'due_diligence.create',
                'due_diligence.update',

                'spms_lookup.view',

                /*
                |--------------------------------------------------------------------------
                | GIS
                |--------------------------------------------------------------------------
                */

                'gis_mapping.view',

                'gis_land_parcels.view',
                'gis_land_parcels.create',
                'gis_land_parcels.update',

                /*
                |--------------------------------------------------------------------------
                | Assets
                |--------------------------------------------------------------------------
                */

                'asset_management.view',

                'land_properties.view',

                'real_estate_projects.view',
                'real_estate_projects.create',
                'real_estate_projects.update',
                'real_estate_projects.submit',
                'real_estate_projects.approve',
                'real_estate_projects.export',
                'lease_agreements.view',
                'lease_agreements.manage',

                'asset_register.view',
                'asset_register.create',
                'asset_register.update',
                'asset_register.export',

                'wayleaves.view',

                /*
                |--------------------------------------------------------------------------
                | Services
                |--------------------------------------------------------------------------
                */

                'services.view',

                /*
                |--------------------------------------------------------------------------
                | Users / Workflow
                |--------------------------------------------------------------------------
                */

                'users.view',
                'users.create',
                'users.update',

                'workflow.view',
                'workflow.manage',

                /*
                |--------------------------------------------------------------------------
                | Configuration
                |--------------------------------------------------------------------------
                */

                'configuration.view',
                'configuration.manage',

                /*
                |--------------------------------------------------------------------------
                | Contracts
                |--------------------------------------------------------------------------
                */

                'contracts.requests.view',
                'contracts.requests.create',
                'contracts.requests.update',
                'contracts.requests.submit',

                'contracts.attachments.view',
                'contracts.attachments.upload',
                'contracts.attachments.download',

                'contracts.classification.view',

                'contracts.contracts.view',

                'contracts.documents.view',
                'contracts.documents.download',

                'contracts.lifecycle.view',

                'contracts.obligations.view',

                'contracts.variations.view',

                'contracts.reports.view',
                'contracts.reports.export',

            ]);


        /*
        |--------------------------------------------------------------------------
        | Acquisition Officer
        |--------------------------------------------------------------------------
        */

        Role::findByName('Acquisition Officer')
            ->syncPermissions([

                'dashboard.view',

                'acquisition_requests.view',
                'acquisition_requests.create',
                'acquisition_requests.update',
                'acquisition_requests.submit',

                'due_diligence.view',
                'due_diligence.create',
                'due_diligence.update',

                'spms_lookup.view',

                'gis_mapping.view',
                'gis_land_parcels.view',

                'acquisition_approvals.view',

                'services.view',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Due Diligence Officer
        |--------------------------------------------------------------------------
        */

        Role::findByName('Due Diligence Officer')
            ->syncPermissions([

                'dashboard.view',

                'acquisition_requests.view',

                'due_diligence.view',
                'due_diligence.create',
                'due_diligence.update',
                'due_diligence.submit',

                'spms_lookup.view',

                'gis_mapping.view',
                'gis_land_parcels.view',

                'due_diligence_approvals.view',
            ]);


        /*
        |--------------------------------------------------------------------------
        | GIS Officer
        |--------------------------------------------------------------------------
        */

        Role::findByName('GIS Officer')
            ->syncPermissions([

                'dashboard.view',

                'gis_mapping.view',

                'gis_land_parcels.view',
                'gis_land_parcels.create',
                'gis_land_parcels.update',
                'gis_land_parcels.delete',
                'gis_land_parcels.export',

                'land_properties.view',
                'asset_register.view',
                'wayleaves.view',

                'acquisition_requests.view',
                'due_diligence.view',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Asset Officer
        |--------------------------------------------------------------------------
        */

        Role::findByName('Asset Officer')
            ->syncPermissions([

                'dashboard.view',

                'asset_management.view',
                'asset_management.create',
                'asset_management.update',

                'land_properties.view',
                'land_properties.create',
                'land_properties.update',

                'real_estate_projects.view',
                'real_estate_projects.create',
                'real_estate_projects.update',
                'real_estate_projects.submit',
                'real_estate_projects.export',

                'asset_register.view',
                'asset_register.create',
                'asset_register.update',
                'asset_register.delete',
                'asset_register.export',

                'wayleaves.view',
                'wayleaves.create',
                'wayleaves.update',
                'wayleaves.delete',

                'asset_management_approvals.view',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Approving Officer
        |--------------------------------------------------------------------------
        */

        Role::findByName('Approving Officer')
            ->syncPermissions([

                'dashboard.view',

                'acquisition_requests.view',

                'acquisition_approvals.view',
                'acquisition_approvals.approve',

                'due_diligence.view',

                'due_diligence_approvals.view',
                'due_diligence_approvals.approve',

                'asset_management.view',

                'asset_management_approvals.view',
                'asset_management_approvals.approve',

                'reports.view',

                /*
                |--------------------------------------------------------------------------
                | Contracts
                |--------------------------------------------------------------------------
                */

                'contracts.requests.view',

                'contracts.classification.view',

                'contracts.contracts.view',
                'contracts.contracts.approve',

                'contracts.documents.view',
                'contracts.documents.download',

                'contracts.reports.view',
                'contracts.reports.export',

            ]);


        /*
        |--------------------------------------------------------------------------
        | Services Officer
        |--------------------------------------------------------------------------
        */

        Role::findByName('Services Officer')
            ->syncPermissions([

                'dashboard.view',

                'services.view',
                'services.create',
                'services.update',

                'spms_lookup.view',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Workflow Administrator
        |--------------------------------------------------------------------------
        */

        Role::findByName('Workflow Administrator')
            ->syncPermissions([

                'dashboard.view',

                'workflow.view',
                'workflow.create',
                'workflow.update',
                'workflow.delete',
                'workflow.manage',

                'configuration_workflow_definitions.view',
                'configuration_workflow_definitions.create',
                'configuration_workflow_definitions.update',
                'configuration_workflow_definitions.delete',
                'configuration_workflow_definitions.manage',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Configuration Administrator
        |--------------------------------------------------------------------------
        */

        Role::findByName('Configuration Administrator')
            ->syncPermissions([

                'dashboard.view',

                'configuration.view',
                'configuration.manage',

                'configuration_sheq_document_ribbons.view',
                'configuration_sheq_document_ribbons.create',
                'configuration_sheq_document_ribbons.update',
                'configuration_sheq_document_ribbons.delete',

                'configuration_payment_modes.view',
                'configuration_payment_modes.create',
                'configuration_payment_modes.update',
                'configuration_payment_modes.delete',

                'configuration_payment_types.view',
                'configuration_payment_types.create',
                'configuration_payment_types.update',
                'configuration_payment_types.delete',

                'configuration_document_types.view',
                'configuration_document_types.create',
                'configuration_document_types.update',
                'configuration_document_types.delete',

                'configuration_units.view',
                'configuration_units.create',
                'configuration_units.update',
                'configuration_units.delete',

                'configuration_ownership_status.view',
                'configuration_ownership_status.create',
                'configuration_ownership_status.update',
                'configuration_ownership_status.delete',

                'configuration_provinces.view',
                'configuration_provinces.create',
                'configuration_provinces.update',
                'configuration_provinces.delete',

                'configuration_services.view',
                'configuration_services.create',
                'configuration_services.update',
                'configuration_services.delete',

                'configuration_team_roles.view',
                'configuration_team_roles.create',
                'configuration_team_roles.update',
                'configuration_team_roles.delete',

                'configuration_workflow_definitions.view',
                'configuration_workflow_definitions.create',
                'configuration_workflow_definitions.update',
                'configuration_workflow_definitions.delete',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Contract Requester
        |--------------------------------------------------------------------------
        */

        Role::findByName('Contract Requester')
            ->syncPermissions([

                'dashboard.view',

                'contracts.requests.view',
                'contracts.requests.create',
                'contracts.requests.update',
                'contracts.requests.submit',

                'contracts.attachments.view',
                'contracts.attachments.upload',
                'contracts.attachments.download',

            ]);


        /*
        |--------------------------------------------------------------------------
        | Contract Supervisor
        |--------------------------------------------------------------------------
        */

        Role::findByName('Contract Supervisor')
            ->syncPermissions([

                'dashboard.view',

                'contracts.requests.view',
                'contracts.requests.review',
                'contracts.requests.return',
                'contracts.requests.reject',
                'contracts.requests.classify',

                'contracts.attachments.view',
                'contracts.attachments.download',

                'contracts.classification.view',

            ]);


        /*
        |--------------------------------------------------------------------------
        | Legal Officer
        |--------------------------------------------------------------------------
        */

        Role::findByName('Legal Officer')
            ->syncPermissions([

                'dashboard.view',

                'contracts.requests.view',

                'contracts.attachments.view',
                'contracts.attachments.download',

                'contracts.classification.view',
                'contracts.classification.create',
                'contracts.classification.update',
                'contracts.classification.manage',

                'contracts.contracts.view',
                'contracts.contracts.create',
                'contracts.contracts.update',

                'contracts.documents.view',
                'contracts.documents.upload',
                'contracts.documents.download',

            ]);


        /*
        |--------------------------------------------------------------------------
        | Procurement Officer
        |--------------------------------------------------------------------------
        */

        Role::findByName('Procurement Officer')
            ->syncPermissions([

                'dashboard.view',

                'contracts.requests.view',

                'contracts.attachments.view',
                'contracts.attachments.download',

                'contracts.classification.view',

                'contracts.contracts.view',

                'contracts.documents.view',
                'contracts.documents.download',

            ]);


        /*
        |--------------------------------------------------------------------------
        | Contract Administrator
        |--------------------------------------------------------------------------
        */

        Role::findByName('Contract Administrator')
            ->syncPermissions([

                'dashboard.view',

                /*
                |--------------------------------------------------------------------------
                | Requests
                |--------------------------------------------------------------------------
                */

                'contracts.requests.view',
                'contracts.requests.create',
                'contracts.requests.update',
                'contracts.requests.submit',
                'contracts.requests.review',
                'contracts.requests.return',
                'contracts.requests.reject',
                'contracts.requests.classify',

                /*
                |--------------------------------------------------------------------------
                | Attachments
                |--------------------------------------------------------------------------
                */

                'contracts.attachments.view',
                'contracts.attachments.upload',
                'contracts.attachments.download',
                'contracts.attachments.delete',

                /*
                |--------------------------------------------------------------------------
                | Classification
                |--------------------------------------------------------------------------
                */

                'contracts.classification.view',
                'contracts.classification.create',
                'contracts.classification.update',
                'contracts.classification.delete',
                'contracts.classification.manage',

                /*
                |--------------------------------------------------------------------------
                | Contracts
                |--------------------------------------------------------------------------
                */

                'contracts.contracts.view',
                'contracts.contracts.create',
                'contracts.contracts.update',
                'contracts.contracts.delete',
                'contracts.contracts.approve',
                'contracts.contracts.manage',

                /*
                |--------------------------------------------------------------------------
                | Documents
                |--------------------------------------------------------------------------
                */

                'contracts.documents.view',
                'contracts.documents.upload',
                'contracts.documents.download',
                'contracts.documents.delete',

                /*
                |--------------------------------------------------------------------------
                | Lifecycle
                |--------------------------------------------------------------------------
                */

                'contracts.lifecycle.view',
                'contracts.lifecycle.manage',

                /*
                |--------------------------------------------------------------------------
                | Obligations
                |--------------------------------------------------------------------------
                */

                'contracts.obligations.view',
                'contracts.obligations.create',
                'contracts.obligations.update',
                'contracts.obligations.delete',
                'contracts.obligations.manage',

                /*
                |--------------------------------------------------------------------------
                | Variations
                |--------------------------------------------------------------------------
                */

                'contracts.variations.view',
                'contracts.variations.create',
                'contracts.variations.update',
                'contracts.variations.delete',
                'contracts.variations.approve',

                /*
                |--------------------------------------------------------------------------
                | Reports
                |--------------------------------------------------------------------------
                */

                'contracts.reports.view',
                'contracts.reports.export',

            ]);
    }
}
