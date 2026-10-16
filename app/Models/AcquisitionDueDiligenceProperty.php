<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcquisitionDueDiligenceProperty extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'acquisition_due_diligence_id',
        'property_label',

        // Planner/Surveyor (repeatable) fields
        'offer_price',
        'findings_report_path',
        'findings_report_comment',
        'filter_viable_option',
        'shortlisted_properties',

        'parcel_plot',
        'parcel_plot_file_path',
        'parcel_plot_comment',
        'coordinates',
        'coordinates_file_path',
        'coordinates_comment',
        'extent',
        'extent_unit',
        'extent_file_path',
        'extent_comment',
        'province',
        'district',
        'township',
        'chiefdom',

        'owner_names',
        'tenure_type',
        'title_type',
        'title_type_other',
        'encumbrances',
        'disputes',
        'disputes_notes',

        'planning_status',
        'zoning',
        'servitudes',
        'environmental_category',
        'settlement_impact',

        'recommendation',
        'mitigation_notes',
        'officer',
        'officer_date',

        'last_saved_at',
    ];

    protected $casts = [
        'servitudes' => 'array',
        'officer_date' => 'date',
        'last_saved_at' => 'datetime',
    ];

    public function dueDiligence()
    {
        return $this->belongsTo(AcquisitionDueDiligence::class, 'acquisition_due_diligence_id');
    }
}
