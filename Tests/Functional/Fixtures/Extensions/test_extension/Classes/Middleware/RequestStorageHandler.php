<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_consent".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\Typo3FormConsent\TestExtension\Middleware;

use Psr\Http\Message;
use Psr\Http\Server;

/**
 * RequestStorageHandler
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class RequestStorageHandler implements Server\MiddlewareInterface
{
    public static ?Message\ServerRequestInterface $request = null;

    public function process(
        Message\ServerRequestInterface $request,
        Server\RequestHandlerInterface $handler,
    ): Message\ResponseInterface {
        self::$request = $request;

        return $handler->handle($request);
    }
}
