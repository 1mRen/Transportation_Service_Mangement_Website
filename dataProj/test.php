<?php
if (function_exists('mysqli_connect')) {
  echo "mysqli is installed";
} else {
  echo "Enable Mysqli support in your PHP installation "; 
}