<?php

/**
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2017, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace SURFnet\VPN\Portal;

use fkooman\OAuth\Server\ClientInfo;

class OAuthClientInfo
{
    /**
     * @param string $clientId
     *
     * @return false|\fkooman\OAuth\Server\ClientInfo
     */
    public static function getClient($clientId)
    {
        $clientInfo = [
            // org.eduvpn.app is DEPRECATED and will be removed once all
            // clients use their new client_id, but we'd have to wait for a
            // new release
            'org.eduvpn.app' => [
                'redirect_uri_list' => [
                    'org.eduvpn.app:/api/callback',
                    'http://127.0.0.1:{PORT}/callback',
                    'http://[::1]:{PORT}/callback',
                ],
                'display_name' => 'eduVPN',
                'require_approval' => false,
            ],
            // Windows
            'org.eduvpn.app.windows' => [
                'redirect_uri_list' => [
                    'org.eduvpn.app:/api/callback',
                    'http://127.0.0.1:{PORT}/callback',
                    'http://[::1]:{PORT}/callback',
                ],
                'display_name' => 'eduVPN for Windows',
                'require_approval' => false,
            ],
            // Android
            'org.eduvpn.app.android' => [
                'redirect_uri_list' => [
                    'org.eduvpn.app:/api/callback',
                ],
                'display_name' => 'eduVPN for Android',
                'require_approval' => false,
            ],
            // iOS
            'org.eduvpn.app.ios' => [
                'redirect_uri_list' => [
                    'org.eduvpn.app:/api/callback',
                ],
                'display_name' => 'eduVPN for iOS',
                'require_approval' => false,
            ],
            // macOS
            'org.eduvpn.app.macos' => [
                'redirect_uri_list' => [
                    'org.eduvpn.app:/api/callback',
                    'http://127.0.0.1:{PORT}/callback',
                    'http://[::1]:{PORT}/callback',
                ],
                'display_name' => 'eduVPN for macOS',
                'require_approval' => false,
            ],
            // Linux
            'org.eduvpn.app.linux' => [
                'redirect_uri_list' => [
                    'org.eduvpn.app:/api/callback',
                    'http://127.0.0.1:{PORT}/callback',
                    'http://[::1]:{PORT}/callback',
                ],
                'display_name' => 'eduVPN for Linux',
                'require_approval' => false,
            ],
        ];

        if (!array_key_exists($clientId, $clientInfo)) {
            return false;
        }

        return new ClientInfo($clientInfo[$clientId]);
    }
}