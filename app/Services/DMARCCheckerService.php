<?php

namespace App\Services;

class DMARCCheckerService
{
    public function check(string $domain): bool
    {
        $records = @dns_get_record(
            '_dmarc.' . $domain,
            DNS_TXT
        );

        if (!$records) {
            return false;
        }

        foreach ($records as $record) {
            if (
                isset($record['txt']) &&
                str_contains($record['txt'], 'v=DMARC1')
            ) {
                return true;
            }
        }

        return false;
    }
}