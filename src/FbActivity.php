<?php

namespace Mortezamasumi\FbActivity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FbActivity
{
    public function getSubjectName(?Model $record, ?string $state): ?string
    {
        if (empty($state)) {
            return '-';
        }

        return Str::of($state)->afterLast('\\')->headline();
    }

    public function getSubject(?Model $record, ?string $state): ?string
    {
        if (empty($state)) {
            return '-';
        }

        $subjectId = $record?->getAttribute('subject_id');

        if ($record && class_exists($state) && is_subclass_of($state, Model::class)) {
            /** @var class-string<Model> $state */
            $subjectModel = $state::query()->whereKey($subjectId)->first();

            $subjectName = $subjectModel?->getAttribute('name')
                ?? $subjectModel?->getAttribute('title')
                ?? $subjectModel?->getAttribute('text')
                ?? '-';
        } else {
            $subjectName = $subjectId;
        }

        $sn = $this->getSubjectName($record, $state);

        if ($sn === '-') {
            return null;
        }

        return __('fb-activity::fb-activity.infolist.subject_name', [
            'a' => $sn,
            'b' => $subjectName ?? '-',
        ]);
    }
}
