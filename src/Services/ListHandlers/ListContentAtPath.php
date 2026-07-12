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

        $templateData = $this->iteratePath($array['templateData'], $path);

        $output = '';
        if (is_array($templateData)) {
            $output .= '<comment>== Properties ==</comment>';
            foreach ($templateData as $key => $value) {
                $value = is_array($value) ? '<comment>..Expand for value..</comment>' : var_export($value, true);
                $output .= '* ' . $key . ' = ' . $value;
            }
        } else {
            $output .= '<comment>== Value ==</comment>';
            $output .= var_export($templateData, true);
        }

        $output .= '<info>When you are done inspecting, run "inspect" to get back to the route selection.</info>';
        return $output;
    }

    public function getHeadline(): string
    {
        return 'Content';
    }
}
