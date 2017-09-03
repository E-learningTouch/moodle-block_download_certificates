<?php

	require_once("../../config.php");

	$fs = get_file_storage();

	$zip = new ZipArchive();
	# create a temp file & open it
	$tmp_file = tempnam('.', '');
	$zip->open($tmp_file, ZipArchive::CREATE);

	$context = context_system::instance();
	$PAGE->set_context($context);
	$sql = "SELECT f.id AS fid, f.userid AS fuserid, f.contextid AS fcontextid, f.filename AS ffilename,
                       ctx.id AS ctxid, ctx.contextlevel AS ctxcontextlevel, ctx.instanceid AS ctxinstanceid,
                       cm.id AS cmid, cm.course AS cmcourse, cm.module AS cmmodule, cm.instance AS cminstance,
                       crt.id AS crtid, crt.course AS crtcourse, crt.name AS crtname, ci.id AS ciid,
					   ci.userid AS ciuserid, ci.certificateid AS cicertificateid, ci.code AS cicode,
					   ci.timecreated AS citimecreated, c.id AS cid, c.fullname AS cfullname,
					   c.shortname AS cshortname
                  FROM {files} f
            INNER JOIN {context} ctx
                    ON ctx.id = f.contextid
            INNER JOIN {course_modules} cm
                    ON cm.id = ctx.instanceid
            INNER JOIN {certificate} crt
                    ON crt.id = cm.instance
             LEFT JOIN {certificate_issues} ci
                    ON ci.certificateid = crt.id
            INNER JOIN {course} c
                    ON c.id = crt.course

				 WHERE f.userid = ci.userid AND
				       f.userid = :userid AND
				    f.component = 'mod_certificate' AND
                     f.mimetype = 'application/pdf'
		      ORDER BY ci.timecreated DESC";
        // PDF FILES ONLY (f.mimetype = 'application/pdf').
	$tempDirName  ="";
    $certificates = $DB->get_records_sql($sql, array('userid' => $USER->id));
    if (!$certificates) {
    		echo 'No certificates to publish.';
            //print_error(get_string('notissuedyet', 'certificate'));
        } else {
        	$dirName = "UserID_".$USER->id."_certificates_can_delete";
		    make_temp_directory($dirName);
		    $tempDirName = "$CFG->tempdir/".$dirName;

            foreach ($certificates as $certdata) {
                $fileinfo = array(
				    'component' => 'mod_certificate',     // usually = table name
				    'filearea' => 'issue',     // usually = table name
				    'itemid' => $certdata->ciid,               // usually = ID of row in table
				    'contextid' => $certdata->ctxid, // ID of context
				    'filepath' => '/',           // any path beginning and ending in /
				    'filename' => $certdata->ffilename); // any filename
				// Get file
				$file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
				                      $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

				//var_dump($file);
				// Read contents
				if ($file) {
				    $contents = $file->get_content();
				    file_put_contents($tempDirName."/".$certdata->ffilename, $contents);
				} else {
				    // file doesn't exist - do something
				}

				$zip->addFile($tempDirName."/".$certdata->ffilename, $certdata->ffilename);
            }
            $zip->close();
        }

        foreach ($certificates as $certdata) {
        	unlink($tempDirName."/".$certdata->ffilename);
        }
		rmdir($tempDirName);

		header('Content-disposition: attachment; filename="All_your_certificates.zip"');
		header('Content-type: application/zip');
		readfile($tmp_file);
		unlink($tmp_file);

?>