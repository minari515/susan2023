const clientConfig = {
	credentials: {
		private_key: process.env.DIALOGFLOW_PRIVATE_KEY!.replace(/\\n/gm, "\n"),
		client_email: process.env.DIALOGFLOW_CLIENT_EMAIL,
	},
	//keyFilename: process.env.DIALOGFLOW_KEYFILE_PATH,
	projectId: process.env.DIALOGFLOW_PROJECT_ID,
};

export default clientConfig;
