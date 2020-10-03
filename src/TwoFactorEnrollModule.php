<?php

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2019, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace LC\Portal;

use LC\Common\Http\HtmlResponse;
use LC\Common\Http\InputValidation;
use LC\Common\Http\RedirectResponse;
use LC\Common\Http\Request;
use LC\Common\Http\Service;
use LC\Common\Http\ServiceModuleInterface;
use LC\Common\Http\SessionInterface;
use LC\Common\Http\UserInfo;
use LC\Common\HttpClient\Exception\ApiException;
use LC\Common\HttpClient\ServerClient;
use LC\Common\TplInterface;
use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Base64;

class TwoFactorEnrollModule implements ServiceModuleInterface
{
    /** @var array<string> */
    private $twoFactorMethods;

    /** @var \LC\Common\Http\SessionInterface */
    private $session;

    /** @var \LC\Common\TplInterface */
    private $tpl;

    /** @var \LC\Common\HttpClient\ServerClient */
    private $serverClient;

    /**
     * @param array<string> $twoFactorMethods
     */
    public function __construct(array $twoFactorMethods, SessionInterface $session, TplInterface $tpl, ServerClient $serverClient)
    {
        $this->twoFactorMethods = $twoFactorMethods;
        $this->session = $session;
        $this->tpl = $tpl;
        $this->serverClient = $serverClient;
    }

    /**
     * @return void
     */
    public function init(Service $service)
    {
        $service->get(
            '/two_factor_enroll',
            /**
             * @return \LC\Common\Http\Response
             */
            function (Request $request, array $hookData) {
                /** @var \LC\Common\Http\UserInfo */
                $userInfo = $hookData['auth'];
                $hasTotpSecret = $this->serverClient->get('has_totp_secret', ['user_id' => $userInfo->getUserId()]);
                $totpSecret = Base32::encodeUpper(random_bytes(20));
                $otpAuthUrl = self::getOtpAuthUrl($request, $userInfo, $totpSecret);

                return new HtmlResponse(
                    $this->tpl->render(
                        'vpnPortalEnrollTwoFactor',
                        [
                            'requireTwoFactorEnrollment' => null !== $this->session->get('_two_factor_enroll_redirect_to'),
                            'twoFactorMethods' => $this->twoFactorMethods,
                            'hasTotpSecret' => $hasTotpSecret,
                            'totpSecret' => $totpSecret,
                            'encodedQrCode' => Base64::encode(Qr::generate($otpAuthUrl)),
                        ]
                    )
                );
            }
        );

        $service->post(
            '/two_factor_enroll',
            /**
             * @return \LC\Common\Http\Response
             */
            function (Request $request, array $hookData) {
                /** @var \LC\Common\Http\UserInfo */
                $userInfo = $hookData['auth'];

                $totpSecret = InputValidation::totpSecret($request->requirePostParameter('totp_secret'));
                $totpKey = InputValidation::totpKey($request->requirePostParameter('totp_key'));

                $redirectTo = $this->session->get('_two_factor_enroll_redirect_to');

                try {
                    $this->serverClient->post('set_totp_secret', ['user_id' => $userInfo->getUserId(), 'totp_secret' => $totpSecret, 'totp_key' => $totpKey]);
                } catch (ApiException $e) {
                    // we were unable to set the OTP secret
                    $hasTotpSecret = $this->serverClient->get('has_totp_secret', ['user_id' => $userInfo->getUserId()]);
                    $otpAuthUrl = self::getOtpAuthUrl($request, $userInfo, $totpSecret);

                    return new HtmlResponse(
                        $this->tpl->render(
                            'vpnPortalEnrollTwoFactor',
                            [
                                'requireTwoFactorEnrollment' => null !== $redirectTo,
                                'twoFactorMethods' => $this->twoFactorMethods,
                                'hasTotpSecret' => $hasTotpSecret,
                                'totpSecret' => $totpSecret,
                                'error_code' => 'invalid_otp_code',
                                'encodedQrCode' => Base64::encode(Qr::generate($otpAuthUrl)),
                            ]
                        )
                    );
                }

                if (null !== $redirectTo) {
                    $this->session->remove('_two_factor_enroll_redirect_to');

                    // mark as 2FA verified
                    $this->session->regenerate();
                    $this->session->set('_two_factor_verified', $userInfo->getUserId());

                    return new RedirectResponse($redirectTo);
                }

                return new RedirectResponse($request->getRootUri().'account', 302);
            }
        );
    }

    /**
     * @param string $totpSecret
     *
     * @return string
     */
    private static function getOtpAuthUrl(Request $request, UserInfo $userInfo, $totpSecret)
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            self::labelEncode($request->getServerName()),
            self::labelEncode($userInfo->getUserId()),
            $totpSecret,
            self::labelEncode($request->getServerName())
        );
    }

    /**
     * @param string $labelStr
     *
     * @return string
     */
    private static function labelEncode($labelStr)
    {
        return rawurlencode(str_replace(':', '_', $labelStr));
    }
}
