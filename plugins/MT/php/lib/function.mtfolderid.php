<?php
# Movable Type (r) Open Source (C) 2001-2011 Six Apart, Ltd.
# This program is distributed under the terms of the
# GNU General Public License, version 2.
#
# $Id$

require_once('function.mtcategoryid.php');
function smarty_function_mtfolderid($args, &$ctx) {
    return smarty_function_mtcategoryid($args, $ctx);
}
?>