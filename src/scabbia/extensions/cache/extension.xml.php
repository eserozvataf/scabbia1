<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>cache</name>
		<version>1.1.0</version>
		<license>GPLv3</license>
		<phpversion>5.3.0</phpversion>
		<phpdependList />
		<fwversion>1.1</fwversion>
		<fwdependList>
			<fwdepend>io</fwdepend>
		</fwdependList>
	</info>
	<eventList>
		<event>
			<name>load</name>
			<callback>Scabbia\Extensions\Cache\cache::extensionLoad</callback>
		</event>
	</eventList>
</scabbia>