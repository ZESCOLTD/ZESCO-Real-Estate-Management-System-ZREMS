<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceFormTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_child_id',
        'form_name',
        'description',
        'sections',
        'is_active',
    ];

    protected $casts = [
        'sections' => 'array',
        'is_active' => 'boolean',
    ];

    public function serviceChild(): BelongsTo
    {
        return $this->belongsTo(ServiceChild::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ServiceFormField::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ServiceFormSubmission::class);
    }

    public function getFieldsBySection(string $section): \Illuminate\Database\Eloquent\Collection
    {
        return $this->fields()->where('section', $section)->get();
    }

    public function getAllSections(): array
    {
        return ServiceFormField::query()
            ->where('service_form_template_id', $this->id)
            ->whereNotNull('section')
            ->selectRaw('section, MIN(sort_order) as first_sort_order')
            ->groupBy('section')
            ->orderBy('first_sort_order')
            ->pluck('section')
            ->values()
            ->toArray();
    }
}
