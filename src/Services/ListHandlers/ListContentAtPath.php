<?php

declare(strict_types=1);

namespace Mamazu\SuluCliBundle\Services\ListHandlers;

use Mamazu\SuluCliBundle\Object\ContentPath;
use Mamazu\SuluCliBundle\Services\PathToNodeConverter;

class ListContentAtPath implements ContentLister
{
    public function __construct(
        private PathToNodeConverter $nodeConverter,
    ) {}

    public function listContent(ContentPath $path, string $stage): string
    {
        $content = $this->nodeConverter->getNodeContent($path, $stage);
        if ($content === null) {
            return '<error>The path does not exists. There is no content here.</error>';
        }
        if (!$path->isInspecting()) {
            return '<comment>There are also properties. To see them use "inspect"</comment>';
        }

        $data = $this->iteratePath($content['templateData'], $path);

        $output = '';
        if (is_array($data)) {
            $output .= '<comment>== Properties ==</comment>'. PHP_EOL;
            foreach ($data as $key => $value) {
                $value = is_array($value) ? '<comment>..Expand for value..</comment>' : var_export($value, true);
                $output .= '* ' . $key . ' = ' . $value.PHP_EOL;
            }
        } else {
            $output .= '<comment>== Value ==</comment>';
            $output .= var_export($data, true);
        }

        $output .= '<info>When you are done inspecting, run "inspect" to get back to the route selection.</info>';
        return $output;
    }

    private function iteratePath(array $data, ContentPath $path): mixed
    {
        $current = $data;
        foreach ($path->getPropertyPathParts() as $pathPart) {
            if (!is_array($current[$pathPart] ?? null)) {
                $current[$pathPart] = [];
            }
            $current = &$current[$pathPart];
        }

        return $current;
    }

    public function getHeadline(): string
    {
        return 'Content';
    }
}
