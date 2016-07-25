AC_INIT([iaq-server], [1.0], [paul.soelder@students.htlinn.ac.at])
AM_INIT_AUTOMAKE([foreign -Wall -Werror])

AC_PROG_MKDIR_P

AC_PATH_PROG(TAR, tar, [error])
if test x"$TAR" = x"error" ; then
	AC_MSG_ERROR([required program 'tar' not found])
fi

AC_CONFIG_FILES([Makefile])

AC_OUTPUT
