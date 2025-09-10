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

        if (class_exists($state)) {
            $subjectModel = $state::find($record->subject_id);

            $subjectName = $subjectModel?->name ?? $subjectModel?->title ?? $subjectModel?->text ?? '-';
        } else {
            $subjectName = $record->subject_id;
        }

        $sn = $this->getSubjectName($record, $state);

        if ($sn === '-') {
            return null;
        }

        return __('fb-activity::fb-activity.infolist.subject_name', [
            'a' => $this->getSubjectName($record, $state),
            'b' => $subjectName,
        ]);
    }
}
