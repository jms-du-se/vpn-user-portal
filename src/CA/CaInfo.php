<?php

declare(strict_types=1);

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2021, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace LC\Portal\CA;

use DateTimeImmutable;
use LC\Portal\Dt;

class CaInfo
{
    private string $pemCert;
    private int $validFrom;
    private int $validTo;

    public function __construct(string $pemCert, int $validFrom, int $validTo)
    {
        $this->pemCert = $pemCert;
        $this->validFrom = $validFrom;
        $this->validTo = $validTo;
    }

    public function pemCert(): string
    {
        return $this->pemCert;
    }

    public function validFrom(): DateTimeImmutable
    {
        return Dt::get('@'.$this->validFrom);
    }

    public function validTo(): DateTimeImmutable
    {
        return Dt::get('@'.$this->validTo);
    }

    public function fingerprint(bool $forHuman = false): string
    {
        $caFingerprint = openssl_x509_fingerprint($this->pemCert, 'sha256');

        return $forHuman ? implode(' ', str_split($caFingerprint, 4)) : $caFingerprint;
    }
}
