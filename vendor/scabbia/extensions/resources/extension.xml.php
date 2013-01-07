<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>resources</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>mime</fwdepend>
			<fwdepend>io</fwdepend>
			<fwdepend>cache</fwdepend>
			<fwdepend>http</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>resources.php</include>
		<include>JSMin.php</include>
		<include>JSMinException.php</include>
		<include>CssMin.php</include>
	</includeList>
	<classList>
		<class>resources</class>
		<class>JSMin</class>
		<class>JSMinException</class>
		<class>CssMin</class>
	</classList>
	<eventList>
		<event>
			<name>httpRoute</name>
			<callback>Scabbia\resources::httpRoute</callback>
		</event>
	</eventList>
</scabbia>