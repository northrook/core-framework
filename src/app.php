<?php

if ( defined( 'DIR_SEP' ) ) {
    echo "DIR_SEP: '".DIR_SEP."' already defined.";
}

defined( 'DIR_SEP' ) || define( 'DIR_SEP', '/' );
