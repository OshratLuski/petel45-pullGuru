<?php

declare(strict_types=1);

namespace local_diagnostic\Clustering\Kmeans;

interface Clusterer
{
    public function cluster(array $samples): array;
}
