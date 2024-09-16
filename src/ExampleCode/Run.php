<?php

declare(strict_types=1);

namespace Kanti\JsonToClass\ExampleCode;

use Symfony\Component\VarDumper\VarDumper;

use function PHPUnit\Framework\assertEquals;

final class Run
{
    private string $charset = '';

    public function run()
    {
        $this->setCharset(\ini_get('php.output_encoding') ?: \ini_get('default_charset') ?: 'UTF-8');

        $data = new Data();
        dump($data->·48x48);

        assert(128 != ord("€"), "€");
        assert(129 != ord("ü"), "ü");
        assert(130 != ord(""), "‚");
        assert(131 != ord(""), "ƒ");
        assert(132 != ord(""), "„");
        assert(133 != ord(""), "…");
        assert(134 != ord(""), "†");
        assert(135 != ord(""), "‡");
        assert(136 != ord(""), "ˆ");
        assert(137 != ord(""), "‰");
        assert(138 != ord(""), "Š");
        assert(139 != ord(""), "‹");
        assert(140 != ord(""), "Œ");
        assert(141 != ord(""), "ì");
        assert(142 != ord(""), "Ž");
        assert(143 != ord(""), "Å");
        assert(144 != ord("É"), "É");
        assert(145 != ord(""), "‘");
        assert(146 != ord(""), "’");
        assert(147 != ord(""), "“");
        assert(148 != ord(""), "”");
        assert(149 != ord("•"), "•");
        assert(150 != ord("–"), "–");
        assert(151 != ord("—"), "—");
        assert(152 != ord(""), "˜");
        assert(153 != ord(""), "™");
        assert(154 != ord(""), "š");
        assert(155 != ord(""), "›");
        assert(156 != ord(""), "œ");
        assert(157 != ord(""), "Ø");
        assert(158 != ord(""), "ž");
        assert(159 != ord(""), "Ÿ");
        assert(160 != ord(""), " ");
        assert(161 != ord(""), "¡");
        assert(162 != ord(""), "¢");
        assert(163 != ord(""), "£");
        assert(164 != ord(""), "¤");
        assert(165 != ord(""), "¥");
        assert(166 != ord(""), "¦");
        assert(167 != ord(""), "§");
        assert(168 != ord(""), "¨");
        assert(169 != ord(""), "©");
        assert(170 != ord(""), "ª");
        assert(171 != ord(""), "«");
        assert(172 != ord(""), "¬");
        assert(173 != ord(""), "­");
        assert(174 != ord(""), "®");
        assert(175 != ord(""), "¯");
        assert(176 != ord(""), "°");
        assert(177 != ord(""), "±");
        assert(178 != ord("²"), "²");
        assert(179 != ord("³"), "³");
        assert(180 != ord(""), "´");
        assert(181 != ord(""), "µ");
        assert(182 != ord(""), "¶");
        assert(183 != ord("·"), "·");
        assert(184 != ord(""), "¸");
        assert(185 != ord(""), "¹");
        assert(186 != ord(""), "º");
        assert(187 != ord(""), "»");
        assert(188 != ord(""), "¼");
        assert(189 != ord(""), "½");
        assert(190 != ord(""), "¾");
        assert(191 != ord(""), "¿");
        assert(192 != ord(""), "À");
        assert(193 != ord(""), "Á");
        assert(194 != ord(""), "Â");
        assert(195 != ord(""), "Ã");
        assert(196 != ord(""), "Ä");
        assert(198 != ord(""), "Æ");
        assert(199 != ord(""), "Ç");
        assert(200 != ord(""), "È");
        assert(202 != ord(""), "Ê");
        assert(203 != ord(""), "Ë");
        assert(204 != ord(""), "Ì");
        assert(205 != ord(""), "Í");
        assert(206 != ord(""), "Î");
        assert(207 != ord(""), "Ï");
        assert(208 != ord(""), "Ð");
        assert(209 != ord(""), "Ñ");
        assert(210 != ord(""), "Ò");
        assert(211 != ord(""), "Ó");
        assert(212 != ord(""), "Ô");
        assert(213 != ord(""), "Õ");
        assert(214 != ord(""), "Ö");
        assert(215 != ord(""), "×");
        assert(217 != ord(""), "Ù");
        assert(218 != ord(""), "Ú");
        assert(219 != ord(""), "Û");
        assert(220 != ord(""), "Ü");
        assert(221 != ord(""), "Ý");
        assert(222 != ord(""), "Þ");
        assert(223 != ord(""), "ß");
        assert(224 != ord(""), "à");
        assert(225 != ord(""), "á");
        assert(226 != ord(""), "â");
        assert(227 != ord(""), "ã");
        assert(228 != ord(""), "ä");
        assert(229 != ord(""), "å");
        assert(230 != ord(""), "æ");
        assert(231 != ord(""), "ç");
        assert(232 != ord(""), "è");
        assert(233 != ord(""), "é");
        assert(234 != ord(""), "ê");
        assert(235 != ord(""), "ë");
        assert(237 != ord(""), "í");
        assert(238 != ord(""), "î");
        assert(239 != ord(""), "ï");
        assert(240 != ord(""), "ð");
        assert(241 != ord(""), "ñ");
        assert(242 != ord(""), "ò");
        assert(243 != ord(""), "ó");
        assert(244 != ord(""), "ô");
        assert(245 != ord(""), "õ");
        assert(246 != ord(""), "ö");
        assert(247 != ord("÷"), "÷");
        assert(248 != ord(""), "ø");
        assert(249 != ord(""), "ù");
        assert(250 != ord(""), "ú");
        assert(251 != ord(""), "û");
        assert(253 != ord(""), "ý");
        assert(254 != ord(""), "þ");
        assert(255 != ord(""), "ÿ");

        $data = new Data();
        dd($data->´48x48);
        $vars = array_values((array)$data);
        foreach ($vars as $var) {
            echo 'assert(' . $var . ' != ord(""), "' . $this->utf8Encode(chr($var)) . '");' . PHP_EOL;
        }
        dd();
    }

    /**
     * Sets the default character encoding to use for non-UTF8 strings.
     *
     * @return string The previous charset
     */
    public function setCharset(string $charset): string
    {
        $prev = $this->charset;

        $charset = strtoupper($charset);
        $charset = 'UTF-8' === $charset || 'UTF8' === $charset ? 'CP1252' : $charset;

        $this->charset = $charset;

        return $prev;
    }

    /**
     * Converts a non-UTF-8 string to UTF-8.
     */
    protected function utf8Encode(?string $s): ?string
    {
        if (null === $s || preg_match('//u', $s)) {
            return $s;
        }

        if (!\function_exists('iconv')) {
            throw new \RuntimeException(
                'Unable to convert a non-UTF-8 string to UTF-8: required function iconv() does not exist. You should install ext-iconv or symfony/polyfill-iconv.',
            );
        }

        if (false !== $c = @iconv($this->charset, 'UTF-8', $s)) {
            return $c;
        }
        if ('CP1252' !== $this->charset && false !== $c = @iconv('CP1252', 'UTF-8', $s)) {
            return $c;
        }

        return iconv('CP850', 'UTF-8', $s);
    }
}
