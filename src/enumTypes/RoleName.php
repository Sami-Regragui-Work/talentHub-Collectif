<?php

namespace App\enumTypes;

enum RoleName: string
{
    case ADMIN = "admin";
    case RECRUITER = "recruiter";
    case CANDIDATE = "candidate";
}
