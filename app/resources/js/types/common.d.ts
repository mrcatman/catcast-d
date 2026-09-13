namespace Common {
	type TextWithParams = string | {
		text: string,
		params: {
			[key: string]: string
		}
	}
}
