{ pkgs ? import <nixpkgs> {} }:

pkgs.mkShell {
  buildInputs = [
    pkgs.php84
  ];

  shellHook = ''
    echo "Welcome to the PHP 8.4 development environment for fortune!";
    which php
  '';
}