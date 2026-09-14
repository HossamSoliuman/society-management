<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes an ActivityLog row on create / update / delete. Models may override
 * activityModule() and activityLabel() for nicer descriptions, and
 * $activityIgnoredAttributes to keep noisy columns out of the change set.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn (Model $model) => $model->logActivity('created'));
        static::updated(function (Model $model) {
            $changes = $model->activityChanges();
            if ($changes !== []) {
                $model->logActivity('updated', $changes);
            }
        });
        static::deleted(fn (Model $model) => $model->logActivity('deleted'));
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    protected function logActivity(string $action, array $properties = []): void
    {
        ActivityLog::record(
            $action,
            $this->activityModule(),
            ucfirst($action).' '.$this->activityLabel(),
            $this,
            $properties,
        );
    }

    protected function activityModule(): string
    {
        return str(class_basename($this))->snake()->replace('_', ' ')->title()->toString();
    }

    protected function activityLabel(): string
    {
        $label = $this->activityIdentifier();

        return strtolower(class_basename($this)).($label !== null ? " {$label}" : " #{$this->getKey()}");
    }

    /**
     * Human identifier used in log descriptions (e.g. an invoice number).
     */
    protected function activityIdentifier(): ?string
    {
        foreach (['name', 'title', 'subscription_number', 'invoice_number', 'receipt_number', 'bill_number', 'email'] as $attribute) {
            if (! empty($this->getAttribute($attribute))) {
                return (string) $this->getAttribute($attribute);
            }
        }

        return null;
    }

    /**
     * Changed attributes (old → new) for update entries.
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    protected function activityChanges(): array
    {
        $ignored = array_merge(['updated_at', 'password', 'remember_token'], $this->activityIgnoredAttributes ?? []);
        $changes = [];

        foreach ($this->getChanges() as $attribute => $new) {
            if (in_array($attribute, $ignored, true)) {
                continue;
            }
            $changes[$attribute] = ['old' => $this->getOriginal($attribute), 'new' => $new];
        }

        return $changes;
    }
}
