<?php

namespace App\Support;

/**
 * SVG chart primitives for the ALG dashboard. Direct port of the chart
 * components from the Claude Design bundle (charts.jsx) so the rendered
 * output matches the design pixel-perfect.
 */
class DashboardCharts
{
    /**
     * "Nice" max for chart axis (1, 2, 5, 10 × power of 10).
     */
    public static function niceMax(float $max): float
    {
        if ($max <= 0) return 10;
        $pow = pow(10, floor(log10($max)));
        $base = $max / $pow;
        $nice = $base <= 1 ? 1 : ($base <= 2 ? 2 : ($base <= 5 ? 5 : 10));
        return $nice * $pow;
    }

    /**
     * Multi-series line/area/bar chart.
     *
     * @param array<string, array<int|float>> $series  e.g. ['organic' => [10,12,...], 'directo' => [...]]
     * @param array<string>                    $labels  X-axis labels (one per data point)
     * @param array<string>                    $colors  CSS colors per series (in $series order)
     */
    public static function multiSeriesSvg(
        array $series,
        array $labels,
        array $colors,
        int $width = 680,
        int $height = 220,
        string $mode = 'line',
        array $padding = ['t' => 16, 'r' => 8, 'b' => 24, 'l' => 36]
    ): string {
        $keys = array_keys($series);
        if (empty($keys) || empty($series[$keys[0]])) {
            return self::emptyChart($width, $height);
        }

        $n = count($series[$keys[0]]);
        $allValues = [];
        foreach ($keys as $k) {
            $allValues = array_merge($allValues, $series[$k]);
        }
        $max = self::niceMax(max(1, max($allValues)));

        $padT = $padding['t']; $padR = $padding['r']; $padB = $padding['b']; $padL = $padding['l'];
        $W = $width - $padL - $padR;
        $H = $height - $padT - $padB;

        $x = fn (int $i): float => $padL + ($n > 1 ? ($i / ($n - 1)) * $W : $W / 2);
        $y = fn (float $v): float => $padT + $H - ($v / $max) * $H;

        $toPath = function (array $arr) use ($x, $y): string {
            $parts = [];
            foreach ($arr as $i => $v) {
                $parts[] = ($i === 0 ? 'M' : 'L') . number_format($x($i), 1, '.', '') . ',' . number_format($y((float)$v), 1, '.', '');
            }
            return implode(' ', $parts);
        };

        $toArea = function (array $arr) use ($toPath, $x, $y, $padT, $H, $n): string {
            return $toPath($arr)
                . ' L' . number_format($x($n - 1), 1, '.', '') . ',' . number_format($padT + $H, 1, '.', '')
                . ' L' . number_format($x(0), 1, '.', '') . ',' . number_format($padT + $H, 1, '.', '')
                . ' Z';
        };

        // Y ticks (5 levels)
        $yTicks = [0, $max * 0.25, $max * 0.5, $max * 0.75, $max];
        // X ticks — first, q1, mid, q3, last (deduped)
        $xTicks = array_values(array_unique([0, intdiv($n, 4), intdiv($n, 2), intdiv(3 * $n, 4), $n - 1]));

        $svg = "<svg width=\"100%\" viewBox=\"0 0 {$width} {$height}\" preserveAspectRatio=\"none\" style=\"display:block;overflow:visible;\">";

        // Y grid + labels
        foreach ($yTicks as $i => $v) {
            $yPos = $y((float)$v);
            $dash = $i === 0 ? '0' : '2,3';
            $label = $v >= 1000 ? number_format($v / 1000, 1) . 'k' : (string)round($v);
            $svg .= "<line x1=\"{$padL}\" x2=\"" . ($width - $padR) . "\" y1=\"{$yPos}\" y2=\"{$yPos}\" stroke=\"#E7E5E4\" stroke-width=\"1\" stroke-dasharray=\"{$dash}\" />";
            $svg .= "<text x=\"" . ($padL - 8) . "\" y=\"" . ($yPos + 3) . "\" text-anchor=\"end\" font-size=\"10\" fill=\"#A8A29E\" font-family=\"'Geist Mono',ui-monospace,monospace\">{$label}</text>";
        }

        // X tick labels
        foreach ($xTicks as $i) {
            if (! isset($labels[$i])) continue;
            $xPos = $x($i);
            $yPos = $height - $padB + 14;
            $lbl = htmlspecialchars((string)$labels[$i], ENT_QUOTES, 'UTF-8');
            $svg .= "<text x=\"{$xPos}\" y=\"{$yPos}\" text-anchor=\"middle\" font-size=\"10\" fill=\"#A8A29E\" font-family=\"'Geist Mono',ui-monospace,monospace\">{$lbl}</text>";
        }

        // Area fill
        if ($mode === 'area') {
            foreach ($keys as $idx => $k) {
                $color = $colors[$idx] ?? '#1E3A8A';
                $area = $toArea($series[$k]);
                $svg .= '<path d="' . $area . '" fill="' . $color . '" fill-opacity="0.10" />';
            }
        }

        // Line series (always rendered, even in area mode)
        foreach ($keys as $idx => $k) {
            $color = $colors[$idx] ?? '#1E3A8A';
            $sw = $idx === 0 ? '1.75' : '1.25';
            $opacity = $idx === 0 ? '1' : '0.7';
            $path = $toPath($series[$k]);
            $svg .= '<path class="alg-sparkline-animate" d="' . $path . '" fill="none" stroke="' . $color . '" stroke-width="' . $sw . '" opacity="' . $opacity . '" />';
        }

        // Bar mode
        if ($mode === 'bars') {
            foreach ($keys as $idx => $k) {
                $color = $colors[$idx] ?? '#1E3A8A';
                $opacity = $idx === 0 ? '1' : '0.55';
                foreach ($series[$k] as $i => $v) {
                    $bx = $x($i) - 1.5 + $idx * 1.2;
                    $by = $y((float)$v);
                    $bh = ($padT + $H) - $by;
                    $svg .= "<rect x=\"{$bx}\" y=\"{$by}\" width=\"1.2\" height=\"{$bh}\" fill=\"{$color}\" opacity=\"{$opacity}\" />";
                }
            }
        }

        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Tiny inline KPI sparkline.
     */
    public static function sparklineSvg(
        array $data,
        string $color = '#2563EB',
        int $width = 72,
        int $height = 24,
        bool $fill = true
    ): string {
        if (empty($data)) {
            return "<svg width=\"{$width}\" height=\"{$height}\"></svg>";
        }
        $max = max($data);
        $min = min($data);
        $r = ($max - $min) ?: 1;
        $n = count($data);
        $x = fn (int $i): float => $n > 1 ? ($i / ($n - 1)) * $width : $width / 2;
        $y = fn (float $v): float => $height - (($v - $min) / $r) * ($height - 4) - 2;

        $parts = [];
        foreach ($data as $i => $v) {
            $parts[] = ($i === 0 ? 'M' : 'L') . number_format($x($i), 1, '.', '') . ',' . number_format($y((float)$v), 1, '.', '');
        }
        $path = implode(' ', $parts);
        $area = $path . " L{$width},{$height} L0,{$height} Z";

        $svg = "<svg width=\"{$width}\" height=\"{$height}\" style=\"display:block\">";
        if ($fill) {
            $svg .= "<path d=\"{$area}\" fill=\"{$color}\" fill-opacity=\"0.10\" />";
        }
        $svg .= "<path class=\"alg-sparkline-animate\" d=\"{$path}\" fill=\"none\" stroke=\"{$color}\" stroke-width=\"1.5\" stroke-linejoin=\"round\" stroke-linecap=\"round\" />";
        $svg .= '</svg>';
        return $svg;
    }

    private static function emptyChart(int $width, int $height): string
    {
        return "<svg width=\"100%\" viewBox=\"0 0 {$width} {$height}\" style=\"display:block\"><text x=\"" . ($width / 2) . "\" y=\"" . ($height / 2) . "\" text-anchor=\"middle\" fill=\"#A8A29E\" font-size=\"12\" font-family=\"'Geist',sans-serif\">Sin datos para este período</text></svg>";
    }
}
