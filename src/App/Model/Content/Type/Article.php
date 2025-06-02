<?php

namespace MobileBike\App\Model\Content\Type;

use MobileBike\App\Model\Content\Content;

class Article extends Content
{
    private string $title;
    private string $description;
    private string $content;
    private \DateTime $date;
}