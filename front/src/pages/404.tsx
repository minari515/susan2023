import AccessExceptions from "@/components/AccessExceptions";

const Custom404 = () => {
	return <AccessExceptions statusCode={404} description='Page Not Found' />;
};

export default Custom404;
