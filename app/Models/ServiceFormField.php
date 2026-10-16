<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_form_template_id',
        'field_name',
        'field_label',
        'field_type',
        'options',
        'is_required',
        'sort_order',
        'section',
        'allow_comments',
        'allow_attachment',
        'allow_multiple_attachments',
        'attachment_label',
        'validation_rules',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'allow_comments' => 'boolean',
        'allow_attachment' => 'boolean',
        'allow_multiple_attachments' => 'boolean',
        'validation_rules' => 'array',
    ];

    /**
     * Safe accessor for `options`.
     *
     * On Oracle the column is CLOB and the `array` cast sometimes returns
     * the raw JSON string (or a stream) instead of an array. Force-decode
     * so callers always receive an array (or null).
     */
    public function getOptionsAttribute($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        // Streams (Oracle CLOB) -> read to string
        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ServiceFormTemplate::class, 'service_form_template_id');
    }

    public function getValidationRulesAttribute($value): array
    {
        $rules = $value ? json_decode($value, true) : [];
        if ($this->is_required && !in_array('required', $rules)) {
            array_unshift($rules, 'required');
        }
        return $rules;
    }
}
