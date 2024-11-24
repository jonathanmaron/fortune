{ pkgs ? import <nixpkgs> {} }:

pkgs.mkShell {
  buildInputs = [
    pkgs.php84
    pkgs.php84Packages.composer
  ];

  shellHook = ''
    clear
    composer --version
    which php
    which composer
    composer update -W
    echo "Welcome to the environment with PHP 8.4 and Composer!"
  '';
}
