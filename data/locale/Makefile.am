NULL =

CLEANFILES =
EXTRA_DIST =

### i18n stuff
# gettextize didn't make much sense for a php project
# so we install the .mo files "manually"
# PENDING(steffen): Improve this

MSGFMT=msgfmt

.po.mo: $<
# Without $(mkinstalldirs) `echo ... | sed ...` a 'make distcheck' would
# fail as the target directory (e.g. php/admin/locale/de/LC_MESSAGES)
# does not yes exist at build time.  Use the `echo ... | sed ...` construction
# as something smarter, like basename, results in configure to complain
# about not portable filenames.
	@$(mkinstalldirs) `echo "$@" | sed 's|messages\.mo||'`
	$(MSGFMT) -o $@ $<

CLEANFILES += */LC_MESSAGES/messages.mo

PHP_LOCALE_DE_PO = de/LC_MESSAGES/messages.po
PHP_LOCALE_DE_MO = de/LC_MESSAGES/messages.mo
PHP_LOCALE_FR_PO = fr/LC_MESSAGES/messages.po
PHP_LOCALE_FR_MO = fr/LC_MESSAGES/messages.mo
PHP_LOCALE_NL_PO = nl/LC_MESSAGES/messages.po
PHP_LOCALE_NL_MO = nl/LC_MESSAGES/messages.mo
PHP_LOCALE_IT_PO = it/LC_MESSAGES/messages.po
PHP_LOCALE_IT_MO = it/LC_MESSAGES/messages.mo
PHP_LOCALE_ES_PO = es/LC_MESSAGES/messages.po
PHP_LOCALE_ES_MO = es/LC_MESSAGES/messages.mo

phplocalededir = $(locale_dir)/de/LC_MESSAGES
phplocalefrdir = $(locale_dir)/fr/LC_MESSAGES
phplocalenldir = $(locale_dir)/nl/LC_MESSAGES
phplocaleitdir = $(locale_dir)/it/LC_MESSAGES
phplocaleesdir = $(locale_dir)/es/LC_MESSAGES

phplocalede_DATA = $(PHP_LOCALE_DE_MO)
phplocalefr_DATA = $(PHP_LOCALE_FR_MO)
phplocalenl_DATA = $(PHP_LOCALE_NL_MO)
phplocaleit_DATA = $(PHP_LOCALE_IT_MO)
phplocalees_DATA = $(PHP_LOCALE_ES_MO)

EXTRA_DIST +=					\
	$(PHP_LOCALE_DE_PO)			\
	$(PHP_LOCALE_FR_PO)			\
	$(PHP_LOCALE_NL_PO)			\
	$(PHP_LOCALE_IT_PO)			\
	$(PHP_LOCALE_ES_PO)			\
	$(NULL)
