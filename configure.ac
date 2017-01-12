AC_PREREQ(2.57)

m4_define(_VERSION,0.4.0)
AC_INIT([kolab-webadmin],[_VERSION],[kolab-devel@kolab.org])
AC_CONFIG_SRCDIR(php/admin/include/ldap.class.php)
AC_CONFIG_AUX_DIR(.)

AM_INIT_AUTOMAKE([gnu] [1.7] [dist-bzip2])
AC_PREFIX_DEFAULT([/usr])

AC_ARG_ENABLE([dist],
  [AC_HELP_STRING([--enable-dist=DIST],
    [distribution target (default: openpkg)])],
  [distribution=dist_conf/$enable_dist],
  [distribution=dist_conf/kolab])
AC_SUBST_FILE(distribution)

AC_ARG_ENABLE([wsdocrootdir],
  [AC_HELP_STRING([--enable-wsdocrootdir=DIR],
    [The webserver document root (default: LOCALSTATEDIR/kolab/www)])],
  [wsdocrootdir="$enable_wsdocrootdir"],
  [wsdocrootdir="\$(localstatedir)/kolab/www"])
AC_SUBST(wsdocrootdir)

AC_ARG_ENABLE([phpdir],
  [AC_HELP_STRING([--enable-phpdir=DIR],
    [The webserver document root (default: LOCALSTATEDIR/kolab/php)])],
  [phpdir="$enable_phpdir"],
  [phpdir="\$(localstatedir)/kolab/php"])
AC_SUBST(phpdir)

AC_ARG_ENABLE([pkgdocdir],
  [AC_HELP_STRING([--enable-pkgdocdir=DIR],
    [where to install the documentation (default: DATADIR/doc)])],
  [pkgdocdir="$enable_pkgdocdir"],
  [pkgdocdir="\$(datadir)/doc/${PACKAGE}"])
AC_SUBST(pkgdocdir)

# Checks for libraries.

# Checks for header files.

# Checks for typedefs, structures, and compiler characteristics.

# Checks for library functions.

# Build date for spec file
spec_build_date=`date '+%Y%m%d'`
AC_SUBST(spec_build_date)

AC_CONFIG_FILES([
Makefile
kolab-webadmin.spec
])

AC_OUTPUT

