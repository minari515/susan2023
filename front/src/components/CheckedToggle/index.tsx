import { CheckedToggleProps } from "./types";

/**
 * @param checked: チェックボックスの状態
 * @param onChange: チェックボックスの状態を変更する関数
 * @param children: チェックボックスのラベル(spanテキストなど想定)
 * @returns トグルスイッチ
 */
const CheckedToggle = ({ checked, onChange, children }: CheckedToggleProps) => {
	return (
		<label
			htmlFor='checked-toggle'
			className='inline-flex relative items-center cursor-pointer gap-2'
		>
			<input
				type='checkbox'
				value=''
				id='checked-toggle'
				className='sr-only peer'
				checked={checked}
				onChange={onChange}
			/>
			<div className="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-400"></div>
			{children}
		</label>
	);
};

export default CheckedToggle;
