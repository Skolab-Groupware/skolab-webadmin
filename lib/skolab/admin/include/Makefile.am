NULL =

CLEANFILES =
EXTRA_DIST =

LIB_KOLABADMIN_INCLUDE_FILES =		\
    auth.class.php			\
    authenticate.php			\
    debug.php				\
    form.class.php			\
    function.kolabsync.php		\
    headers.php				\
    ldap.class.php			\
    locale.php				\
    menu.php				\
    mysmarty.php			\
    passwd.php				\
    Sieve.php				\
    $(NULL)

libkolabadminincludedir = $(phplib_dir)/skolab/admin/include
libkolabadmininclude_DATA = $(LIB_KOLABADMIN_INCLUDE_FILES)

LIB_KOLABADMIN_CONFIG_FILES =		\
    config.php				\
    session_vars.php			\
    $(NULL)

libkolabadminconfigdir = $(sysconfdir)/skolab/webadmin
libkolabadminconfig_DATA = $(LIB_KOLABADMIN_CONFIG_FILES)

config.php: config.php.in
	sed -e 's|\@webserver_document_root\@|${webserver_document_root}|g'     \
	   -e 's|\@webadminui_alias\@|${webadminui_alias}|g'                   \
	   -e 's|\@phplib_dir\@|${phplib_dir}|g'                               \
	   -e 's|\@locale_dir\@|${locale_dir}|g'                               \
	   -e 's|\@smarty_compiledir\@|${smarty_compiledir}|g'                               \
	< config.php.in >$@

CLEANFILES += config.php
EXTRA_DIST += config.php.in
