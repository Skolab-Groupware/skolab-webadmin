AC_PREREQ(2.57)

m4_define(_VERSION,2.4.0)
AC_INIT([skolab-webadmin],[_VERSION],[https://github.com/sunweaver/skolab-webadmin])
AC_CONFIG_AUX_DIR([build-aux])

AM_INIT_AUTOMAKE([gnu] [1.7] [dist-bzip2] [no-dist-gzip] [subdir-objects])
AC_PREFIX_DEFAULT([/usr/local])

AC_ARG_WITH([webserver_document_root],
  [AC_HELP_STRING([--with-webserver_document_root=DIR],
    [root dir for webserver documents (default: /usr/share/$PACKAGE/htdocs))])],
  [webserver_document_root=$withval],
  [webserver_document_root=/usr/share/$PACKAGE/htdocs])
AC_SUBST(webserver_document_root)

AC_ARG_WITH([webadminui_alias],
  [AC_HELP_STRING([--with-webadminui_alias=URL_PATH],
    [URL path of WebAdmin UI (default: /PACKAGE_TARNAME))])],
  [webadminui_alias=$withval],
  [webadminui_alias=/$PACKAGE_TARNAME])
AC_SUBST(webadminui_alias)

AC_ARG_WITH([smarty_templates_dir],
  [AC_HELP_STRING([--with-smarty_templates_dir=DIR],
    [Directory where the Smarty template files are (default: /usr/share/$PACKAGE/templates))])],
  [smarty_templates_dir=$withval],
  [smarty_templates_dir=/usr/share/$PACKAGE/templates])
AC_SUBST(smarty_templates_dir)

AC_ARG_WITH([smarty_compiledir],
  [AC_HELP_STRING([--with-smarty_compiledir=DIR],
    [Directory for compiled Smarty templates (default: /var/cache/PACKAGE_TARNAME/templates))])],
  [smarty_compiledir=$withval],
  [smarty_compiledir=/var/cache/$PACKAGE_TARNAME/templates])
AC_SUBST(smarty_compiledir)

AC_ARG_WITH([locale_dir],
  [AC_HELP_STRING([--with-locale_dir=DIR],
    [Directory where the translation files are located (default: /usr/share/$PACKAGE/locale))])],
  [locale_dir=$withval],
  [locale_dir=/usr/share/$PACKAGE/locale])
AC_SUBST(locale_dir)

AC_ARG_WITH([phplib_dir],
  [AC_HELP_STRING([--with-phplib_dir=DIR],
    [Directory where the translation files are located (default: /usr/share/php))])],
  [phplib_dir=$withval],
  [phplib_dir=/usr/share/php])
AC_SUBST(phplib_dir)


AC_ARG_WITH([webserver_user],
  [AC_HELP_STRING([--with-webserver_user=USER],
    [User account the webserver runs under (default: www-data))])],
  [webserver_user=$withval],
  [webserver_user=www-data])
AC_SUBST(webserver_user)


#kolab_php_smarty_prefix
#webserver_web_prefix
#phplibdir
#phplibdir2

AC_CONFIG_FILES([
data/locale/Makefile
data/templates/Makefile
data/Makefile
lib/Skolab/Admin/Sieve/Segment/Makefile
lib/Skolab/Admin/Sieve/Makefile
lib/Skolab/Admin/include/Makefile
lib/Skolab/Admin/Makefile
lib/Skolab/Makefile
lib/Makefile
test/Makefile
htdocs/about/Makefile
htdocs/administrator/Makefile
htdocs/addressbook/Makefile
htdocs/distributionlist/Makefile
htdocs/domainmaintainer/Makefile
htdocs/images/Makefile
htdocs/maintainer/Makefile
htdocs/settings/Makefile
htdocs/sharedfolder/Makefile
htdocs/user/Makefile
htdocs/Makefile
Makefile
])

AC_OUTPUT
