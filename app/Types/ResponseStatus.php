<?php

namespace App\Types;

final class ResponseStatus
{
    const OK_STATUS = 200;
    const CREATED_STATUS = 201;
    const ACCEPTED_STATUS = 202;
    const BAD_REQUEST_STATUS = 400;
    const UNAUTHORIZED_STATUS = 401;
    const FORBIDDEN_STATUS = 403;
    const NOT_FOUND_STATUS = 404;
    const INVALID_INPUT = 422;
    const SERVER_ERROR = 500;
}
