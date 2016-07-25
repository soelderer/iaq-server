# Indoor Air Quality Server

This software is part of a diploma thesis about indoor air quality.

The indoor air quality server (iaq-server) collects measurement data from
indoor air quality measurement devices running the indoor air quality
measurement daemon (iaq-measurementd), stores the measurements in a database
and displays them on a website.

See https://github.com/soelderer/iaq-measurementd for the measurement software.

# Installation

You may want to use the Debian binary package:
https://diplomarbeit.soelder.xyz/releases

Alternatively the installation process using the autotools is described below.
You can also copy the files manually, for sure. Note that the jpgraph library
needs to be in libs/jpgraph of the website root directory, the images in img.

## Download

Get the latest release from https://diplomarbeit.soelder.xyz/releases

## Install

1) Unpack the archive

$ tar xf iaq-server-x.x.tar.gz

2) Change to directory created

$ cd iaq-server-x.x

3) Run configure

$ ./configure

4) Install
As root:
$ make install

# Legal

iaq-server is released under the terms of the BSD 3-Clause License. A
full copy of the license is included in the COPYING file.
