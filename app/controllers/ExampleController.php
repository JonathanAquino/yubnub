<?php

/**
 * Implementations of some example commands.
 */
class ExampleController extends Controller {

    /**
     * SYNOPSIS
     *         tr FROM TO TEXT
     *
     * EXAMPLES
     *         tr en fr hello
     *
     * DESCRIPTION
     *         Translates the given text from the first language to the
     *         second language, courtesy of Google Language Tools.
     *         The above example translates "hello" from English (en) to
     *         French (fr). Not all language combinations are available.
     *
     *         Chinese (Simplified) zh-CN
     *         English ............ en
     *         French ............. fr
     *         German ............. de
     *         Italian ............ it
     *         Japanese ........... ja
     *         Korean ............. ko
     *         Portuguese ......... pt
     *         Spanish ............ es
     */
    public function action_tr() {
        $args = preg_split('/\s+/', trim($_GET['args']));
        $this->render('tr', array(
            'from' => $args[0],
            'to' => $args[1],
            'text' => implode(' ', array_slice($args, 2)),
        ));
    }

    /**
     * SYNOPSIS
     *         dnow [YYYY-MMDD]
     *
     * EXAMPLE
     *         dnow 2005-0607
     *         dnow
     *
     * DESCRIPTION
     *         Play the Democracy Now archive from archive.org. Democracy Now
     *         is a daily radio and TV news program on over 300 stations,
     *         pioneering the largest community media collaboration in the US.
     *
     *         Specify a date in YYYY-MMDD format. Or leave out the date
     *         for today's show.
     */
    public function action_dnow() {
        $yyyy_mmdd = isset($_GET['args']) ? $_GET['args'] : '';
        if (!$yyyy_mmdd) {
            $yyyy_mmdd = date('Y-md');
        }
        $this->redirectTo("http://www.archive.org/download/dn{$yyyy_mmdd}/dn{$yyyy_mmdd}_vbr.m3u");
    }

    /**
     * NAME
     *        echo - display a line of text
     *
     * SYNOPSIS
     *        echo [STRING]
     *
     * EXAMPLE
     *        echo Hello, World!
     */
    public function action_echo() {
        echo isset($_GET['text']) ? $_GET['text'] : '';
    }

    /**
     * NAME
     *        date - print the system date and time
     *
     * SYNOPSIS
     *        date -format [FORMAT] -offset [DAYS]
     *
     * EXAMPLES
     *        date
     *        date -format m/d/Y -offset -7
     *
     * DESCRIPTION
     *        Displays the current date and time, offset by the given
     *        number of DAYS. The FORMAT is given by the codes below:
     *
     *        a       The abbreviated weekday name ("Sun")
     *        A       The full weekday name ("Sunday")
     *        b       The abbreviated month name ("Jan")
     *        B       The full month name ("January")
     *        c       The preferred local date and time representation
     *        d       Day of the month (01..31)
     *        H       Hour of the day, 24-hour clock (00..23)
     *        I       Hour of the day, 12-hour clock (01..12)
     *        j       Day of the year (001..366)
     *        m       Month of the year (01..12)
     *        M       Minute of the hour (00..59)
     *        p       Meridian indicator ("AM" or "PM")
     *        S       Second of the minute (00..60)
     *        U       Week number of the current year, starting with the first Sunday as the first day of the first week (00..53)
     *        W       Week number of the current year, starting with the first Monday as the first day of the first week (00..53)
     *        w       Day of the week (Sunday is 0, 0..6)
     *        x       Preferred representation for the date alone, no time
     *        X       Preferred representation for the time alone, no date
     *        y       Year without a century (00..99)
     *        Y       Year with century
     *        Z       Time zone name
     */
    public function action_today() {
        $time = time();
        $offset = isset($_GET['offset']) ? $_GET['offset'] : '';
        if (strlen($offset) > 0) {
            $time += 86400 * $offset;
        }
        $format = isset($_GET['format']) ? $_GET['format'] : '';
        if (strlen($format) == 0) {
            echo '';
            return;
        }
        echo strftime(preg_replace('/[aAbBcdHIjmMpSUWwxXyYZ]/', '%$0', $format), $time);
    }

    /**
     * NAME
     *        ucase - convert text to uppercase
     *
     * SYNOPSIS
     *        ucase [STRING]
     *
     * EXAMPLE
     *        ucase Hello, World!
     */
    public function action_ucase() {
        $text = isset($_GET['text']) ? $_GET['text'] : '';
        echo mb_strtoupper($text);
    }

    /**
     * NAME
     *        lcase - convert text to lowercase
     *
     * SYNOPSIS
     *        lcase [STRING]
     *
     * EXAMPLE
     *        lcase Hello, World!
     */
    public function action_lcase() {
        $text = isset($_GET['text']) ? $_GET['text'] : '';
        echo mb_strtolower($text);
    }

    /**
     * NAME
     *        split - displays several webpages on a single screen
     *
     * SYNOPSIS
     *        split [URL] [URL] [URL] ...
     *
     * EXAMPLE
     *        split http://google.com http://yahoo.com http://answers.com
     *        split {url g porsche} {url y porsche} {url a porsche}
     */
    public function action_split() {
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $urls = isset($_GET['urls']) ? $_GET['urls'] : '';
        $urls = preg_split('/\s+/', $urls);
        if ($type == 'h') {
            $columnCount = 1;
        } elseif ($type == 'v') {
            $columnCount = count($urls);
        } else {
            $columnCount = ceil(sqrt(count($urls)));
        }
        $rows = array();
        foreach ($urls as $url) {
            if (count($rows) == 0 || count($rows[count($rows)-1]['columns']) == $columnCount) {
                $rows[] = array('columnPercentages' => '', 'columns' => '');
            }
            $rows[count($rows)-1]['columns'][] = $url;
        }
        $rowCount = count($rows);
        $rowPercentages = array_fill(0, $rowCount, (floor(100/$rowCount)) . '%');
        foreach ($rows as $i => $row) {
            $columnCount = count($rows[$i]['columns']);
            $columnPercentages = array_fill(0, $columnCount, (floor(100/$columnCount)) . '%');
            $rows[$i]['columnPercentages'] = implode(',', $columnPercentages);
        }
        $this->render('split', array(
            'rows' => $rows,
            'rowPercentages' => implode(',', $rowPercentages),
        ));
    }

}
