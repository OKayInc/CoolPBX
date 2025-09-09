<?php

namespace App\Repositories;

use App\Models\Stream;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;

class StreamRepository
{
    protected $model;

    public function __construct(Stream $stream)
    {
        $this->model = $stream;
    }

    public function getAll(string $domainUuid): Collection
    {
        return $this->model->where('domain_uuid', $domainUuid)->get();
    }

    public function findByUuid(string $uuid): ?Stream
    {
        return $this->model->where('stream_uuid', $uuid)->first();
    }

    public function create(array $data): Stream
    {
        return $this->model->create($data);
    }

    public function update(Stream $stream, array $data): bool
    {
        return $stream->update($data);
    }

    public function delete(Stream $stream): ?bool
    {
        return $stream->delete();
    }

    public function getOptions(): array
    {
        if (!class_exists(Stream::class)) {
            return [];
        }

        $streams = Stream::where(function ($query) {
            $query->where("domain_uuid", Session::get("domain_uuid"))
                ->orWhereNull("domain_uuid");
        })
            ->where("stream_enabled", "true")
            ->orderBy("stream_name", "asc")
            ->get();

        if ($streams->isEmpty()) {
            return [];
        }

        $values = [];

        foreach ($streams as $stream) {
            $values[] = [
                "id" => $stream->stream_location,
                "name" => $stream->stream_name
            ];
        }

        return [
            "label" => __("Streams"),
            "values" => $values
        ];
    }
}
