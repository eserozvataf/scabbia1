<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>media</name>
		<version>1.1.0</version>
		<license>GPLv3</license>
		<phpversion>5.3.0</phpversion>
		<phpdependList />
		<fwversion>1.1</fwversion>
		<fwdependList />
	</info>
	<eventList>
		<event>
			<name>load</name>
			<type>callback</type>
			<value>Scabbia\Extensions\Media\media::extensionLoad</value>
		</event>
	</eventList>
</scabbia>