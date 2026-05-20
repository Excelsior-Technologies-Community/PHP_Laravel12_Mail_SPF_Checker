<?php

namespace App\Services;

class DKIMCheckerService
{
    public function check(string $domain): bool
    {
        $selectors = ['default', 'google', 'selector1'];

        foreach ($selectors as $selector) {
            $records = @dns_get_record(
                $selector . '._domainkey.' . $domain,
                DNS_TXT
            );

            if ($records) {
                foreach ($records as $record) {
                    if (
                        isset($record['txt']) &&
                        str_contains($record['txt'], 'v=DKIM1')
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}