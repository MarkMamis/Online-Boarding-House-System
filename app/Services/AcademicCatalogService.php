<?php

namespace App\Services;

class AcademicCatalogService
{
    private static ?array $cachedCatalog = null;

    public static function getCatalog(): array
    {
        if (self::$cachedCatalog !== null) {
            return self::$cachedCatalog;
        }

        $path = resource_path('data/academic_catalog.json');

        if (!is_file($path)) {
            return self::$cachedCatalog = self::defaultCatalog();
        }

        $json = @file_get_contents($path);
        if ($json === false) {
            return self::$cachedCatalog = self::defaultCatalog();
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return self::$cachedCatalog = self::defaultCatalog();
        }

        return self::$cachedCatalog = [
            'colleges' => is_array($decoded['colleges'] ?? null) ? $decoded['colleges'] : [],
            'programs' => is_array($decoded['programs'] ?? null) ? $decoded['programs'] : [],
            'majors' => is_array($decoded['majors'] ?? null) ? $decoded['majors'] : [],
        ];
    }

    public static function programsForCollege(?string $collegeCode): array
    {
        $catalog = self::getCatalog();
        $code = trim((string) $collegeCode);

        $programs = $catalog['programs'][$code] ?? [];
        return is_array($programs) ? array_values($programs) : [];
    }

    public static function majorsForProgram(?string $programName): array
    {
        $catalog = self::getCatalog();
        $program = trim((string) $programName);

        $majors = $catalog['majors'][$program] ?? [];
        return is_array($majors) ? array_values($majors) : [];
    }

    public static function inferCollegeByProgram(?string $programName): ?string
    {
        $program = trim((string) $programName);
        if ($program === '') {
            return null;
        }

        foreach (self::getCatalog()['programs'] as $collegeCode => $programs) {
            if (is_array($programs) && in_array($program, $programs, true)) {
                return (string) $collegeCode;
            }
        }

        return null;
    }

    private static function defaultCatalog(): array
    {
        return [
            'colleges' => [],
            'programs' => [],
            'majors' => [],
        ];
    }
}
