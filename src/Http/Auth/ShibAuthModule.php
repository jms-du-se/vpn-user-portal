<?php

declare(strict_types=1);

/*
 * eduVPN - End-user friendly VPN.
 *
 * Copyright: 2016-2021, The Commons Conservancy eduVPN Programme
 * SPDX-License-Identifier: AGPL-3.0+
 */

namespace LC\Portal\Http\Auth;

use LC\Portal\Http\Request;
use LC\Portal\Http\UserInfo;
use LC\Portal\Http\UserInfoInterface;

class ShibAuthModule extends AbstractAuthModule
{
    private string $userIdAttribute;

    /** @var array<string> */
    private array $permissionAttributeList;

    public function __construct(string $userIdAttribute, array $permissionAttributeList)
    {
        $this->userIdAttribute = $userIdAttribute;
        $this->permissionAttributeList = $permissionAttributeList;
    }

    public function userInfo(Request $request): ?UserInfoInterface
    {
        $permissionList = [];
        foreach ($this->permissionAttributeList as $permissionAttribute) {
            if (null !== $permissionAttributeValue = $request->optionalHeader($permissionAttribute)) {
                $permissionList = array_merge($permissionList, explode(';', $permissionAttributeValue));
            }
        }

        return new UserInfo(
            $request->requireHeader($this->userIdAttribute),
            $permissionList
        );
    }
}
