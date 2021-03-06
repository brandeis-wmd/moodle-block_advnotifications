<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


defined('MOODLE_INTERNAL') || die;


/**
 * Class advnotificationsrss extends base blocks class. Initialise and render notifications.
 *
 * @copyright  2016 onwards LearningWorks Ltd {@link https://learningworks.co.nz/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// mostly copied from block rss_client
class advnotificationsrss extends block_base
{
    /** The maximum time in seconds that cron will wait between attempts to retry failing RSS feeds. */
    const CLIENT_MAX_SKIPTIME = 43200; // 60 * 60 * 12 seconds.

    /** @var bool track whether any of the output feeds have recorded failures */
    private $hasfailedfeeds = false;

    // does nothing but required
    public function init() {
        return true;
    }

    /**
     * Returns the html of a feed to be displaed in the block
     *
     * @param mixed feedrecord The feed record from the database
     * @param int maxentries The maximum number of entries to be displayed
     * @param boolean showtitle Should the feed title be displayed in html
     * @return block_rss_client\output\feed|null The renderable feed or null of there is an error
     */
    public function get_feed($feedrecord, $maxentries, $showtitle) {
        global $CFG;
        require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

        #if ($feedrecord->skipuntil) {
        if (method_exists($feedrecord, 'skipuntil') and ($feedrecord->skipuntil)) {
            // Last attempt to gather this feed via cron failed - do not try to fetch it now.
            $this->hasfailedfeeds = true;
            return null;
        }

        $simplepiefeed = new moodle_simplepie($feedrecord->url);

        if(isset($CFG->block_advnotifications_timeout)){
            $simplepiefeed->set_cache_duration($CFG->block_advnotifications_timeout * 60);
        }

        if ($simplepiefeed->error()) {
            debugging($feedrecord->url .' Failed with code: '.$simplepiefeed->error());
            return null;
        }

        if(empty($feedrecord->preferredtitle)){
            // Simplepie does escape HTML entities.
            $feedtitle = $this->format_title($simplepiefeed->get_title());
        }else{
            // Moodle custom title does not does escape HTML entities.
            $feedtitle = $this->format_title(s($feedrecord->preferredtitle));
        }

        if (empty($this->config->title)){
            //NOTE: this means the 'last feed' displayed wins the block title - but
            //this is exiting behaviour..
            $this->title = strip_tags($feedtitle);
        }

        // FIXME: $config var changed to 0
        // $feed = new \block_advnotifications\output\feed($feedtitle, $showtitle, $this->config->block_advnotifications_show_channel_image);
        $feed = new \block_advnotifications\output\feed($feedtitle, $showtitle, 0);

        if ($simplepieitems = $simplepiefeed->get_items(0, $maxentries)) {
            foreach ($simplepieitems as $simplepieitem) {
                try {
                    $item = new \block_advnotifications\output\item(
                        $simplepieitem->get_id(),
                        new moodle_url($simplepieitem->get_link()),
                        $simplepieitem->get_title(),
                        $simplepieitem->get_description(),
                        new moodle_url($simplepieitem->get_permalink()),
                        $simplepieitem->get_date('U'),
                        // FIXME: just commented out
                        // $this->config->display_description
                        ''
                    );

                    $feed->add_item($item);
                } catch (moodle_exception $e) {
                    // If there is an error with the RSS item, we don't
                    // want to crash the page. Specifically, moodle_url can
                    // throw an exception of the param is an extremely
                    // malformed url.
                    debugging($e->getMessage());
                }
            }
        }

        // Feed image.
        if ($imageurl = $simplepiefeed->get_image_url()) {
            try {
                $image = new \block_advnotifications\output\channel_image(
                    new moodle_url($imageurl),
                    $simplepiefeed->get_image_title(),
                    new moodle_url($simplepiefeed->get_image_link())
                );

                $feed->set_image($image);
            } catch (moodle_exception $e) {
                // If there is an error with the RSS image, we don'twant to
                // crash the page. Specifically, moodle_url can throw an
                // exception if the param is an extremely malformed url.
                debugging($e->getMessage());
            }
        }
        return $feed;
    }

    /**
     * Strips a large title to size and adds ... if title too long
     * This function does not escape HTML entities, so they have to be escaped
     * before being passed here.
     *
     * @param string title to shorten
     * @param int max character length of title
     * @return string title shortened if necessary
     */
    function format_title($title,$max=64) {

        if (core_text::strlen($title) <= $max) {
            return $title;
        } else {
            return core_text::substr($title, 0, $max - 3) . '...';
        }
    }

    /**
     * cron - goes through all the feeds. If the feed has a skipuntil value
     * that is less than the current time cron will attempt to retrieve it
     * with the cache duration set to 0 in order to force the retrieval of
     * the item and refresh the cache.
     *
     * If a feed fails then the skipuntil time of that feed is set to be
     * later than the next expected cron time. The amount of time will
     * increase each time the fetch fails until the maximum is reached.
     *
     * If a feed that has been failing is successfully retrieved it will
     * go back to being handled as though it had never failed.
     *
     * CRON should therefor process requests for permanently broken RSS
     * feeds infrequently, and temporarily unavailable feeds will be tried
     * less often until they become available again.
     *
     * @return boolean Always returns true
     */
    function cron() {
        global $CFG, $DB;
        require_once($CFG->libdir.'/simplepie/moodle_simplepie.php');

        // Get the legacy cron time, strangely the cron property of block_base
        // does not seem to get set. This means we must retrive it here.
        $this->cron = $DB->get_field('block', 'cron', array('name' => 'advnotifications'));

        // We are going to measure execution times
        $starttime =  microtime();
        $starttimesec = time();

        // Fetch all site feeds.
        $rs = $DB->get_recordset('block_advnotificationsrss');
        $counter = 0;
        mtrace('');
        foreach ($rs as $rec) {
            mtrace('    ' . $rec->url . ' ', '');

            // Skip feed if it failed recently.
            if ($starttimesec < $rec->skipuntil) {
                mtrace('skipping until ' . userdate($rec->skipuntil));
                continue;
            }

            // Fetch the rss feed, using standard simplepie caching
            // so feeds will be renewed only if cache has expired
            core_php_time_limit::raise(60);

            $feed =  new moodle_simplepie();
            // set timeout for longer than normal to be agressive at
            // fetching feeds if possible..
            $feed->set_timeout(40);
            $feed->set_cache_duration(0);
            $feed->set_feed_url($rec->url);
            $feed->init();

            if ($feed->error()) {
                // Skip this feed (for an ever-increasing time if it keeps failing).
                $rec->skiptime = $this->calculate_skiptime($rec->skiptime);
                $rec->skipuntil = time() + $rec->skiptime;
                $DB->update_record('block_advnotificationsrss', $rec);
                mtrace("Error: could not load/find the RSS feed - skipping for {$rec->skiptime} seconds.");
            } else {
                mtrace ('ok');
                // It worked this time, so reset the skiptime.
                if ($rec->skiptime > 0) {
                    $rec->skiptime = 0;
                    $rec->skipuntil = 0;
                    $DB->update_record('block_advnotificationsrss', $rec);
                }
                // Only increase the counter when a feed is sucesfully refreshed.
                $counter ++;
            }
        }
        $rs->close();

        // Show times
        mtrace($counter . ' feeds refreshed (took ' . microtime_diff($starttime, microtime()) . ' seconds)');

        return true;
    }

    /**
     * Calculates a new skip time for a record based on the current skip time.
     *
     * @param int $currentskip The curreent skip time of a record.
     * @return int A new skip time that should be set.
     */
    protected function calculate_skiptime($currentskip) {
        // The default time to skiptime.
        $newskiptime = $this->cron * 1.1;
        if ($currentskip > 0) {
            // Double the last time.
            $newskiptime = $currentskip * 2;
        }
        if ($newskiptime > self::CLIENT_MAX_SKIPTIME) {
            // Do not allow the skip time to increase indefinatly.
            $newskiptime = self::CLIENT_MAX_SKIPTIME;
        }
        return $newskiptime;
    }


    /**
     * Gets feed description from first available element
     *
     * @param string $fURL feed URL
     * @return string first available feed description from URL
     */

    public function retrieve_feed_description($fURL) {
        global $DB, $USER;
                             // get record from db or insert it
        $advnotrss = $DB->get_record(
            'block_advnotificationsrss',
            array('url'=>$fURL),
            $fields='*',
            $strictness=IGNORE_MISSING
        );
        if(! $advnotrss) {
            $advnotrss = new stdClass();
            $advnotrss->userid = $USER->id;
            $advnotrss->url = $fURL;
            $advnotrss->shared = 0;
            $advnotrss_id = $DB->insert_record('block_advnotificationsrss', $advnotrss);
        }

        $updatedmsg = false;
        $rawFeed = $this->get_feed($advnotrss, 1, true);
        if(is_null($rawFeed)) {
            return $updatedmsg;
        }
        $items = $rawFeed->get_items();
        foreach($items as $item) {
            $description = $item->get_description();
            if(strlen($description) > 0) {
                $updatedmsg = $description;
                break;
            }
        }

        return $updatedmsg;
    }
}
