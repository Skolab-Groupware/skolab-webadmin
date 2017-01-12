AC_PREREQ(2.57)

m4_define(_VERSION,2.2.0)
AC_INIT([kolab-webadmin],[_VERSION],[kolab-devel@kolab.org])
AC_CONFIG_AUX_DIR(.)

AM_INIT_AUTOMAKE([gnu] [1.7] [dist-bzip2] [no-dist-gzip])
AC_PREFIX_DEFAULT([/usr])

AC_ARG_WITH([dist],
  [AC_HELP_STRING([--with-dist=DIST],
    [distribution target (default: openpkg)])],
  [distribution=$srcdir/dist_conf/$withval],
  [distribution=$srcdir/dist_conf/kolab])

AS_IF([test -f $distribution],,
	AC_MSG_ERROR([incorrect value for --with-dist: $withval])
)
AC_SUBST_FILE(distribution)

common=dist_conf/common
AC_SUBST_FILE(common)

AC_ARG_WITH([openpkg],
  [AC_HELP_STRING([--with-openpkg],
    [Special Files for the openpkg platform])],
  [case "${withval}" in
    yes) openpkg=true ;;
	 no)  openpkg=false ;;
	 *)   AC_MSG_ERROR(bad value ${withval} for --with-openpkg) ;;
  esac],[openpkg=true])
AM_CONDITIONAL(OPENPKG, test x$openpkg = xtrue)

if test "x$openpkg" = "xtrue"; then
  WITHOPENPKG=yes
else
  WITHOPENPKG=no
fi
AC_SUBST(WITHOPENPKG)

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

