##
##  kolab-webadmin.spec -- OpenPKG RPM Specification
##  Copyright (c) 2004 Klaraelvdalens Datakonsult AB <http://www.klaralvdalens-datakonsult.se>
##
##  Permission to use, copy, modify, and distribute this software for
##  any purpose with or without fee is hereby granted, provided that
##  the above copyright notice and this permission notice appear in all
##  copies.
##
##  THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESSED OR IMPLIED
##  WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
##  MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
##  IN NO EVENT SHALL THE AUTHORS AND COPYRIGHT HOLDERS AND THEIR
##  CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
##  SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
##  LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF
##  USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
##  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
##  OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT
##  OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
##  SUCH DAMAGE.
##

#   package information
Name:         kolab-webadmin
Summary:      Kolab Groupware Server Web Administration Interface
URL:          http://www.kolab.org/
Vendor:       Klaraelvdalens Datakonsult AB
Packager:     Klaraelvdalens Datakonsult AB
Distribution: OpenPKG
Group:        Mail
License:      GPL
Version:      2.2.1
Release:      20090304


#   list of sources
Source0:      kolab-webadmin-%{version}.tar.bz2

#   build information
Prefix:       %{l_prefix}
BuildRoot:    %{l_buildroot}
BuildPreReq:  OpenPKG, openpkg >= 20070603
PreReq:       OpenPKG, openpkg >= 20070603
PreReq:       kolabd >= 2.2.1-20081212
PreReq:       php-smarty >= 2.6.20
AutoReq:      no
AutoReqProv:  no

%option       kolab_version snapshot

%description
	Web based administration interface for The Kolab Groupware Server

%prep
    %setup -q
    %{l_shtool} subst -e 's;@kolab_version@;%{kolab_version};g' \
	www/admin/kolab/versions.php.in

%build
    ./configure -prefix=%{l_prefix} --with-dist=kolab

%install

    #   install package
    %{l_make} %{l_mflags} install \
	DESTDIR=$RPM_BUILD_ROOT

    #   generate file list
    %{l_rpmtool} files -v -ofiles -r$RPM_BUILD_ROOT %{l_files_std} \
	%dir '%defattr(-,%{l_nusr},%{l_ngrp})' %{l_prefix}/var/kolab/php/admin/templates_c

%files -f files

%clean
    rm -rf $RPM_BUILD_ROOT
